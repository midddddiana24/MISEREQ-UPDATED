<section class="panel admin-record-filter-panel" aria-label="Record filters">
    <form method="GET" action="{{ route($indexRoute) }}">
        <div class="admin-record-filter-grid">
            <div class="field admin-record-filter-search">
                <label for="record_search">Search records</label>
                <input id="record_search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $searchPlaceholder }}">
            </div>
            <div class="field">
                <label for="record_group">Group</label>
                <select id="record_group" name="group">
                    @foreach($groupOptions as $value => $label)
                        <option value="{{ $value }}" @selected($group === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="record_status">Status</label>
                <select id="record_status" name="status">
                    <option value="">All statuses</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-record-filter-actions">
                <button type="submit" class="btn-primary">Search</button>
                <a href="{{ route($indexRoute) }}" class="btn-ghost">Reset</a>
            </div>
        </div>
        <details class="admin-disclosure admin-filter-advanced" @if(! empty($filters['date_from']) || ! empty($filters['date_to'])) open @endif>
            <summary>More filters</summary>
            <div class="form-grid admin-filter-advanced-fields">
                <div class="field form-col-4"><label for="record_date_from">{{ $dateLabel }} from</label><input id="record_date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="field form-col-4"><label for="record_date_to">{{ $dateLabel }} to</label><input id="record_date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></div>
            </div>
        </details>
    </form>
</section>
