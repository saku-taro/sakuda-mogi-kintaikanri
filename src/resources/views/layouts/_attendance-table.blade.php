<table class="attendance-table">
    <tr class="attendance-table__row">
        <th class="attendance-table__header">名前</th>
        <td class="attendance-table__cell attendance-table__cell--name">{{ $user->name }}</td>
    </tr>
    <tr class="attendance-table__row">
        <th class="attendance-table__header">日付</th>
        <td class="attendance-table__cell attendance-table__cell--date">
            <span class="date-year">{{ $date->format('Y年') }}</span>
            <span class="date-md">{{ $date->format('n月j日') }}</span>
        </td>
    </tr>
    <tr class="attendance-table__row">
        <th class="attendance-table__header">出勤・退勤</th>
        <td class="attendance-table__cell">
            @if($isEditable)
                <div class="attendance-table__cell-content">
                    <div class="attendance-table__row-wrapper">
                        <input class="attendance-table__input {{ $attendance?->clock_in ? '' : 'is-empty' }}" type="time" name="clock_in" value="{{ old('clock_in', $attendance?->clock_in?->format('H:i')) }}">
                        <span class="attendance-table__separator">～</span>
                        <input class="attendance-table__input {{ $attendance?->clock_out ? '' : 'is-empty' }}" type="time" name="clock_out" value="{{ old('clock_out', $attendance?->clock_out?->format('H:i')) }}">
                    </div>
                    @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            @else
                <span class="attendance-table__time">
                    {{ $attendanceRequest->after_data['clock_in'] ?? '--:--' }}
                </span>
                <span class="attendance-table__separator">～</span>
                <span class="attendance-table__time">
                {{ $attendanceRequest->after_data['clock_out'] ?? '--:--' }}
                </span>
            @endif
        </td>
    </tr>

    @php
        $breaks = $isEditable ? ($attendance?->breakRecords ?? collect()) : ($attendanceRequest->after_data['breaks'] ?? []);
        $maxBreaks = $isEditable ? (count($breaks) + 1) : count($breaks);
    @endphp

    {{-- @for ($i = 0; $i < $maxBreaks; $i++)
        <tr class="attendance-table__row">
            <th class="attendance-table__header">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
            <td class="attendance-table__cell">
                @if($isEditable)
                    <div class="attendance-table__cell-content">
                        <div class="attendance-table__row-wrapper">
                            @php $break = $breaks[$i] ?? null; @endphp
                            <input class="attendance-table__input {{ $break?->break_in ? '' : 'is-empty' }}" type="time" name="breaks[{{ $i }}][break_in]" value="{{ old("breaks.$i.break_in", $break?->break_in?->format('H:i')) }}">
                            <span class="attendance-table__separator">～</span>
                            <input class="attendance-table__input {{ $break?->break_out ? '' : 'is-empty' }}" type="time" name="breaks[{{ $i }}][break_out]" value="{{ old("breaks.$i.break_out", $break?->break_out?->format('H:i')) }}">
                            <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break?->id ?? '' }}">
                        </div>
                        @error("breaks.$i.break_in")
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error("breaks.$i.break_out")
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <span class="attendance-table__time">
                        {{ $breaks[$i]['break_in'] ?? '--:--' }}
                    </span>
                    <span class="attendance-table__separator">～</span>
                    <span class="attendance-table__time">
                        {{ $breaks[$i]['break_out'] ?? '--:--' }}
                    </span>
                @endif
            </td>
        </tr>
    @endfor --}}

    @for ($i = 0; $i < $maxBreaks; $i++)
        @php
            $break = $breaks[$i] ?? null;
            // 休憩データが空かどうかを判定
            $breakIn = $isEditable ? ($break?->break_in) : ($break['break_in'] ?? null);
            $breakOut = $isEditable ? ($break?->break_out) : ($break['break_out'] ?? null);
            $isEmpty = empty($breakIn) && empty($breakOut);
        @endphp

        {{-- 両方空の場合はスキップする --}}
        @if($isEmpty && !$isEditable)
            @continue
        @endif

        <tr class="attendance-table__row">
            <th class="attendance-table__header">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
            <td class="attendance-table__cell">
                @if($isEditable)
                    <div class="attendance-table__cell-content">
                        <div class="attendance-table__row-wrapper">
                            <input class="attendance-table__input {{ $break?->break_in ? '' : 'is-empty' }}" type="time" name="breaks[{{ $i }}][break_in]" value="{{ old("breaks.$i.break_in", $break?->break_in?->format('H:i')) }}">
                            <span class="attendance-table__separator">～</span>
                            <input class="attendance-table__input {{ $break?->break_out ? '' : 'is-empty' }}" type="time" name="breaks[{{ $i }}][break_out]" value="{{ old("breaks.$i.break_out", $break?->break_out?->format('H:i')) }}">
                            <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break?->id ?? '' }}">
                        </div>
                        @error("breaks.$i.break_in") <div class="error-message">{{ $message }}</div> @enderror
                        @error("breaks.$i.break_out") <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                @else
                    <span class="attendance-table__time">{{ $break['break_in'] ?? '--:--' }}</span>
                    <span class="attendance-table__separator">～</span>
                    <span class="attendance-table__time">{{ $break['break_out'] ?? '--:--' }}</span>
                @endif
            </td>
        </tr>
    @endfor

    <tr class="attendance-table__row">
        <th class="attendance-table__header">備考</th>
        <td class="attendance-table__cell">
            @if($isEditable)
                <div class="attendance-table__cell-content">
                    <div class="attendance-table__row-wrapper">
                        <textarea class="attendance-table__textarea" name="remarks">{{ old('remarks', $attendance?->remarks ?? '') }}</textarea>
                    </div>
                    @error('remarks')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            @else
                {{ $attendanceRequest?->reason ?? '' }}
            @endif
        </td>
    </tr>
</table>
