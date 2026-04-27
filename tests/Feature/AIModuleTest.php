<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\User;
use App\Services\AIServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class AIModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_analyze_uses_cache_for_same_user_and_idea(): void
    {
        Cache::flush();
        config([
            'services.openai.builder.cache_ttl_seconds' => 300,
            'services.openai.rate_limits.builder_per_minute' => 50,
        ]);

        $user = User::factory()->create();
        $idea = 'Build an inventory dashboard with multilingual support.';

        $mock = Mockery::mock(AIServiceInterface::class);
        $mock->shouldReceive('chat')->andReturn(['reply' => 'unused']);
        $mock->shouldReceive('analyzeProject')->once()->andReturn([
            'project_type' => 'Web Application',
            'difficulty' => 'Intermediate',
            'estimated_timeline' => ['weeks' => 12, 'label' => '12 weeks (estimated)'],
            'step_by_step_plan' => ['Define scope', 'Build MVP'],
            'risks' => ['Scope creep'],
        ]);
        $mock->shouldReceive('generateBudget')->once()->andReturn([
            'currency' => 'USD',
            'minimum_budget' => 5000,
            'ideal_budget' => 20000,
            'estimated_hours' => 240,
            'component_cost_breakdown' => [],
            'cost_saving_alternatives' => [],
            'breakdown' => [],
        ]);
        $mock->shouldReceive('suggestTools')->once()->andReturn([
            'categories' => [],
            'recommended_stack' => [],
            'primary_tools' => ['Laravel'],
            'cheap_alternatives' => [],
            'free_software_alternatives' => [],
            'diy_options' => [],
            'local_sourcing_suggestions' => [],
        ]);
        $this->app->instance(AIServiceInterface::class, $mock);

        $first = $this->actingAs($user)->postJson(route('builder.analyze'), ['idea' => $idea]);
        $second = $this->actingAs($user)->postJson(route('builder.analyze'), ['idea' => $idea]);

        $first->assertOk()->assertJsonPath('success', true);
        $second->assertOk()->assertJsonPath('success', true);
    }

    public function test_builder_analyze_is_rate_limited(): void
    {
        Cache::flush();
        config([
            'services.openai.builder.cache_ttl_seconds' => 0,
            'services.openai.rate_limits.builder_per_minute' => 2,
        ]);

        $user = User::factory()->create();

        $mock = Mockery::mock(AIServiceInterface::class);
        $mock->shouldReceive('chat')->andReturn(['reply' => 'unused']);
        $mock->shouldReceive('analyzeProject')->times(2)->andReturn([
            'project_type' => 'Web Application',
            'difficulty' => 'Intermediate',
            'estimated_timeline' => ['weeks' => 12, 'label' => '12 weeks (estimated)'],
            'step_by_step_plan' => ['Define scope'],
            'risks' => ['Scope creep'],
        ]);
        $mock->shouldReceive('generateBudget')->times(2)->andReturn([
            'currency' => 'USD',
            'minimum_budget' => 5000,
            'ideal_budget' => 20000,
            'estimated_hours' => 240,
            'component_cost_breakdown' => [],
            'cost_saving_alternatives' => [],
            'breakdown' => [],
        ]);
        $mock->shouldReceive('suggestTools')->times(2)->andReturn([
            'categories' => [],
            'recommended_stack' => [],
            'primary_tools' => ['Laravel'],
            'cheap_alternatives' => [],
            'free_software_alternatives' => [],
            'diy_options' => [],
            'local_sourcing_suggestions' => [],
        ]);
        $this->app->instance(AIServiceInterface::class, $mock);

        $this->actingAs($user)->postJson(route('builder.analyze'), ['idea' => 'Build a basic ERP portal'])->assertOk();
        $this->actingAs($user)->postJson(route('builder.analyze'), ['idea' => 'Build a logistics analytics app'])->assertOk();
        $this->actingAs($user)->postJson(route('builder.analyze'), ['idea' => 'Build a multilingual CRM suite'])->assertStatus(429);
    }

    public function test_chat_send_is_rate_limited_and_sanitized(): void
    {
        Cache::flush();
        config([
            'services.openai.rate_limits.chat_per_minute' => 1,
            'services.openai.rate_limits.chat_per_hour' => 5,
            'services.openai.rate_limits.chat_per_session_per_minute' => 1,
            'services.openai.chat_async' => false,
        ]);

        $user = User::factory()->create();
        $session = ChatSession::query()->create([
            'user_id' => $user->id,
            'title' => 'Test Session',
        ]);

        $mock = Mockery::mock(AIServiceInterface::class);
        $mock->shouldReceive('chat')->once()->andReturn([
            'reply' => "<b>Hello</b>\n\nworld",
        ]);
        $mock->shouldReceive('analyzeProject')->andReturn([]);
        $mock->shouldReceive('generateBudget')->andReturn([]);
        $mock->shouldReceive('suggestTools')->andReturn([]);
        $mock->shouldReceive('analyzeProjectIdea')->andReturn([]);
        $this->app->instance(AIServiceInterface::class, $mock);

        $sendRoute = route('chat.messages.send', ['chatSession' => $session->id]);

        $first = $this->actingAs($user)->postJson($sendRoute, [
            'message' => "<script>alert('x')</script>Hello",
        ]);
        $first->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'message' => "alert('x')Hello",
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender' => 'ai',
            'message' => "Hello\n\nworld",
        ]);

        $this->actingAs($user)->postJson($sendRoute, ['message' => 'Second request'])->assertStatus(429);
    }

    public function test_chat_send_supports_async_processing_mode(): void
    {
        Cache::flush();
        config([
            'services.openai.chat_async' => true,
            'services.openai.rate_limits.chat_per_minute' => 10,
            'services.openai.rate_limits.chat_per_hour' => 10,
            'services.openai.rate_limits.chat_per_session_per_minute' => 10,
        ]);

        $user = User::factory()->create();
        $session = ChatSession::query()->create([
            'user_id' => $user->id,
            'title' => 'Async Session',
        ]);

        $mock = Mockery::mock(AIServiceInterface::class);
        $mock->shouldReceive('chat')->once()->andReturn([
            'reply' => 'Async reply complete.',
        ]);
        $mock->shouldReceive('analyzeProject')->andReturn([]);
        $mock->shouldReceive('generateBudget')->andReturn([]);
        $mock->shouldReceive('suggestTools')->andReturn([]);
        $mock->shouldReceive('analyzeProjectIdea')->andReturn([]);
        $this->app->instance(AIServiceInterface::class, $mock);

        $response = $this->actingAs($user)->postJson(
            route('chat.messages.send', ['chatSession' => $session->id]),
            ['message' => 'Run async response']
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.queued', true);

        $this->assertDatabaseCount('chat_messages', 2);
        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'message' => 'Run async response',
        ]);
    }
}
