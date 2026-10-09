<!-- ========== Left Sidebar Start ========== -->

<style>
    #sidebar-menu > ul > li
    {
        margin-bottom: 6px;
        border-bottom: 1px solid #e9ecef;
    }
</style>

<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu">Main</li>

                <li>
                    <a href="{{ route('admin.dashboard.index') }}">
                        <i class="fa-solid fa-house"></i>
                        <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                <li class="menu-title" data-key="t-platform">Platform Core</li>

                <li>
                    <a href="{{ route('admin.settings.index') }}">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Platform Settings</span>
                    </a>
                </li>

                <!-- Landing Page CMS -->
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fa-solid fa-laptop-code"></i>
                        <span>Landing Page CMS</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.hero.index') }}"><i class="fa-solid fa-heading me-1"></i> Hero Section</a></li>
                        <li><a href="{{ route('admin.features.index') }}"><i class="fa-solid fa-list-check me-1"></i> Features</a></li>
                        <li><a href="{{ route('admin.steps.index') }}"><i class="fa-solid fa-route me-1"></i> How It Works</a></li>
                        <li><a href="{{ route('admin.use-cases.index') }}"><i class="fa-solid fa-briefcase me-1"></i> Use Cases</a></li>
                        <li><a href="{{ route('admin.store-demos.index') }}"><i class="fa-solid fa-desktop me-1"></i> Store Demos</a></li>
                        <li><a href="{{ route('admin.testimonials.index') }}"><i class="fa-solid fa-quote-left me-1"></i> Testimonials</a></li>
                        <li><a href="{{ route('admin.faqs.index') }}"><i class="fa-solid fa-circle-question me-1"></i> FAQs</a></li>
                        <li><a href="{{ route('admin.matrix.index') }}"><i class="fa-solid fa-chart-simple me-1"></i> Platform Metrics</a></li>
                    </ul>
                </li>

                <!-- Plans & Subscriptions -->
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Plans & Billing</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.plans.index') }}"><i class="fa-solid fa-tags me-1"></i> Subscription Plans</a></li>
                        <li><a href="{{ route('admin.subscriptions.index') }}"><i class="fa-solid fa-file-contract me-1"></i> Merchant Subscriptions</a></li>
                        <li><a href="{{ route('admin.invoices.index') }}"><i class="fa-solid fa-receipt me-1"></i> Invoices</a></li>
                    </ul>
                </li>

                <!-- Tenant Management -->
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fa-solid fa-store"></i>
                        <span>Tenant Operations</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.domains.index') }}"><i class="fa-solid fa-globe me-1"></i> Domains & SSL</a></li>
                        <li><a href="{{ route('admin.usages.index') }}"><i class="fa-solid fa-database me-1"></i> Resource Usage</a></li>
                    </ul>
                </li>

                <!-- Content & Support -->
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fa-solid fa-folder-open"></i>
                        <span>Content & Support</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.pages.index') }}"><i class="fa-solid fa-file-lines me-1"></i> Custom Pages</a></li>
                        <li><a href="{{ route('admin.contacts.index') }}"><i class="fa-solid fa-envelope me-1"></i> Contact Messages</a></li>
                        <li><a href="{{ route('admin.newsletter.index') }}"><i class="fa-solid fa-paper-plane me-1"></i> Newsletter Subs</a></li>
                    </ul>
                </li>

                <!-- Admin Security -->
                <li class="menu-title" data-key="t-system">System Administration</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin Security</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.admins.index') }}"><i class="fa-solid fa-users me-1"></i> Admin Users</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->
