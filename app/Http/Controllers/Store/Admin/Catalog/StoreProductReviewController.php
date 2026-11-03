<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreProductReviewController extends Controller
{
    private function tenantId(): int
    {
        return tenantID();
    }

    /**
     * Display a listing of product reviews with moderation filters and analytics.
     */
    public function index(Request $request): View
    {
        $tenantId = $this->tenantId();

        $query = ProductReview::query()
            ->where('tenant_id', $tenantId)
            ->with(['product', 'customer', 'order', 'repliedBy']);

        // 1. Status Filter
        $status = $request->input('status', 'all');
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        // 2. Rating Filter
        $rating = $request->input('rating', 'all');
        if (in_array((string)$rating, ['1', '2', '3', '4', '5'], true)) {
            $query->where('rating', (int)$rating);
        }

        // 3. Product Filter
        $productId = $request->input('product_id', 'all');
        if ($productId !== 'all' && is_numeric($productId)) {
            $query->where('product_id', (int)$productId);
        }

        // 4. Verified Purchase Filter
        $verified = $request->input('verified', 'all');
        if ($verified === '1' || $verified === 'yes') {
            $query->where('is_verified_purchase', true);
        } elseif ($verified === '0' || $verified === 'no') {
            $query->where('is_verified_purchase', false);
        }

        // 5. Date Range Filter
        $dateRange = $request->input('date_range', 'all');
        if ($dateRange === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateRange === 'last_7_days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($dateRange === 'this_month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        }

        // 6. Search Filter (Reviewer, Comment, Title, Product Name)
        if ($search = trim((string)$request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('reviewer_name', 'like', "%{$search}%")
                  ->orWhere('reviewer_email', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('generic_name', 'like', "%{$search}%");
                  });
            });
        }

        // 7. Sort
        $sort = $request->input('sort', 'latest');
        if ($sort === 'oldest') {
            $query->oldest('created_at');
        } elseif ($sort === 'rating_high') {
            $query->orderByDesc('rating')->latest('created_at');
        } elseif ($sort === 'rating_low') {
            $query->orderBy('rating')->latest('created_at');
        } else {
            $query->latest('created_at');
        }

        $reviews = $query->paginate(15)->withQueryString();

        // ── KPIs & Analytics ──
        $baseTenantReviews = ProductReview::where('tenant_id', $tenantId);
        $totalReviews = (clone $baseTenantReviews)->count();
        $pendingReviews = (clone $baseTenantReviews)->where('status', 'pending')->count();
        $approvedReviews = (clone $baseTenantReviews)->where('status', 'approved')->count();
        $rejectedReviews = (clone $baseTenantReviews)->where('status', 'rejected')->count();

        $avgRating = round((float) ((clone $baseTenantReviews)->where('status', 'approved')->avg('rating') ?? 0), 1);
        if ($avgRating == 0 && $totalReviews > 0) {
            $avgRating = round((float) ((clone $baseTenantReviews)->avg('rating') ?? 0), 1);
        }

        // Star breakdown
        $starBreakdown = [
            5 => (clone $baseTenantReviews)->where('rating', 5)->count(),
            4 => (clone $baseTenantReviews)->where('rating', 4)->count(),
            3 => (clone $baseTenantReviews)->where('rating', 3)->count(),
            2 => (clone $baseTenantReviews)->where('rating', 2)->count(),
            1 => (clone $baseTenantReviews)->where('rating', 1)->count(),
        ];

        // Store products for filter select
        $products = Product::where('tenant_id', $tenantId)
            ->select('id', 'name', 'generic_name')
            ->orderBy('name')
            ->get();

        return view('store_admin.pages.reviews.index', compact(
            'reviews',
            'products',
            'totalReviews',
            'pendingReviews',
            'approvedReviews',
            'rejectedReviews',
            'avgRating',
            'starBreakdown'
        ));
    }

    /**
     * Show single review details (supports AJAX modal).
     */
    public function show(ProductReview $review, Request $request): JsonResponse|View
    {
        $this->authorizeTenant($review);
        $review->load(['product.media', 'customer', 'order', 'repliedBy']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'comment' => $review->comment,
                    'status' => $review->status,
                    'is_verified_purchase' => $review->is_verified_purchase,
                    'reviewer_name' => $review->reviewer_display_name,
                    'reviewer_email' => $review->reviewer_email,
                    'reviewer_avatar' => $review->reviewer_avatar,
                    'admin_reply' => $review->admin_reply,
                    'replied_at' => $review->replied_at?->format('M d, Y h:i A'),
                    'replied_by' => $review->repliedBy?->name,
                    'images' => $review->images ?? [],
                    'created_at' => $review->created_at->format('M d, Y h:i A'),
                    'created_diff' => $review->created_at->diffForHumans(),
                    'product' => [
                        'id' => $review->product->id,
                        'name' => $review->product->name,
                        'generic_name' => $review->product->generic_name,
                        'thumbnail' => $review->product->thumbnail_url,
                        'url' => route('store.admin.products.edit', $review->product->id),
                    ],
                ],
            ]);
        }

        return view('store_admin.pages.reviews.show', compact('review'));
    }

    /**
     * Update the moderation status (approved, pending, rejected).
     */
    public function updateStatus(Request $request, ProductReview $review): JsonResponse|RedirectResponse
    {
        $this->authorizeTenant($review);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $review->update(['status' => $validated['status']]);

        $message = "Review status updated to " . ucfirst($validated['status']) . ".";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $review->status,
                'status_badge' => $review->status_badge,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Add or update pharmacist / merchant reply.
     */
    public function reply(Request $request, ProductReview $review): JsonResponse|RedirectResponse
    {
        $this->authorizeTenant($review);

        $validated = $request->validate([
            'admin_reply' => ['required', 'string', 'max:3000'],
            'auto_approve' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'admin_reply' => trim($validated['admin_reply']),
            'replied_at' => now(),
            'replied_by' => auth()->id(),
        ];

        if (!empty($validated['auto_approve']) && $review->status === 'pending') {
            $updateData['status'] = 'approved';
        }

        $review->update($updateData);

        $message = 'Pharmacist response posted successfully.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'admin_reply' => $review->admin_reply,
                'replied_at' => $review->replied_at->format('M d, Y h:i A'),
                'replied_by' => auth()->user()->name ?? 'Pharmacist',
                'status' => $review->status,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove the review.
     */
    public function destroy(ProductReview $review, Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeTenant($review);

        $review->delete();

        $message = 'Product review deleted successfully.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk actions (Approve, Reject, Delete).
     */
    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:approve,reject,pending,delete'],
        ]);

        $tenantId = $this->tenantId();
        $query = ProductReview::where('tenant_id', $tenantId)->whereIn('id', $validated['ids']);
        $count = $query->count();

        if ($count === 0) {
            $message = 'No valid reviews selected.';
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        switch ($validated['action']) {
            case 'approve':
                $query->update(['status' => 'approved']);
                $message = "Successfully approved {$count} review(s).";
                break;
            case 'reject':
                $query->update(['status' => 'rejected']);
                $message = "Successfully rejected {$count} review(s).";
                break;
            case 'pending':
                $query->update(['status' => 'pending']);
                $message = "Successfully moved {$count} review(s) to pending.";
                break;
            case 'delete':
                $query->delete();
                $message = "Successfully deleted {$count} review(s).";
                break;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Enforce multitenant isolation.
     */
    private function authorizeTenant(ProductReview $review): void
    {
        if ($review->tenant_id !== $this->tenantId()) {
            abort(404, 'Review not found for this store.');
        }
    }
}
