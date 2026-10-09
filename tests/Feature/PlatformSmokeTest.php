<?php

namespace Tests\Feature;

use App\Models\PlatformNewsletterSubscriber;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlatformSmokeTest extends TestCase
{
    private function host(): string
    {
        return 'http://'.config('app.base_domain');
    }

    public function test_marketing_pages_render_with_cms_props(): void
    {
        $this->get($this->host().'/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('platform/index')
                ->has('hero')
                ->has('stats', 4)
                ->has('features')
                ->has('steps')
                ->has('brand')
            );

        $this->get($this->host().'/features')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('platform/features')
                ->has('useCases')
                ->has('featureGrid')
            );

        $this->get($this->host().'/pricing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('platform/pricing')
                ->has('plans')
                ->has('comparison')
                ->has('faqs')
            );
    }

    public function test_blog_index_and_show_render(): void
    {
        $this->get($this->host().'/blog')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('platform/blog')
                ->has('featured')
                ->has('posts')
                ->has('categories')
            );

        $this->get($this->host().'/blog/launch-your-first-store-in-a-weekend')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('platform/blog/show')
                ->where('post.slug', 'launch-your-first-store-in-a-weekend')
            );
    }

    public function test_newsletter_subscription_persists(): void
    {
        $email = 'smoke-'.uniqid().'@example.com';

        $this->from($this->host().'/')
            ->post($this->host().'/newsletter', ['email' => $email])
            ->assertRedirect();

        $this->assertDatabaseHas('platform_newsletter_subscribers', [
            'email' => $email,
            'is_subscribed' => 1,
        ]);

        PlatformNewsletterSubscriber::where('email', $email)->delete();
    }
}
