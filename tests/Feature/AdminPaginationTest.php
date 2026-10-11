<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
{
    public function test_compact_messenger_pagination_uses_text_controls_and_preserves_filters(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 20), 129, 20, 1, [
            'path' => 'http://127.0.0.1:8000/admin/messenger',
        ]);
        $paginator->appends(['scope' => 'unread', 'search' => 'network']);

        $html = $paginator->links('admin.partials.pagination', [
            'compact' => true,
            'paginationLabel' => 'Conversation pages',
        ])->render();

        $this->assertStringContainsString('Showing 1 to 20 of 129 results', $html);
        $this->assertStringContainsString('Page 1 of 7', $html);
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringContainsString('scope=unread', $html);
        $this->assertStringContainsString('search=network', $html);
        $this->assertStringContainsString('page=2', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }

    public function test_audit_pagination_has_numbered_links_and_a_current_page(): void
    {
        $paginator = new LengthAwarePaginator(range(26, 50), 75, 25, 2, [
            'path' => 'http://127.0.0.1:8000/admin/operations',
            'pageName' => 'activity_page',
        ]);
        $paginator->appends(['activity_module' => 'system']);

        $html = $paginator->links('admin.partials.pagination', [
            'paginationLabel' => 'Audit activity pages',
        ])->render();

        $this->assertStringContainsString('Showing 26 to 50 of 75 results', $html);
        $this->assertStringContainsString('aria-current="page">2', $html);
        $this->assertStringContainsString('activity_page=3', $html);
        $this->assertStringContainsString('activity_module=system', $html);
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }
}
