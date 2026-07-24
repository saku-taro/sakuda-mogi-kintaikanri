{{-- <table class="attendance-table">
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
                        {{ $attendance?->clock_in?->format('H:i') ?? '' }}
                    </span>
                    <span class="attendance-table__separator">～</span>
                    <span class="attendance-table__time">
                        {{ $attendance?->clock_out?->format('H:i') ?? '' }}
                </span>
            @endif
        </td>
    </tr>

@php
        $breaks = $attendance ? $attendance->getDisplayBreaks($isEditable) : collect();
        $maxBreaks = $attendance ? $attendance->getDisplayMaxBreaks($isEditable) : 1;
    @endphp

    @for ($i = 0; $i < $maxBreaks; $i++)
        @php
            $break = $breaks[$i] ?? null;
        @endphp

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
                    <span class="attendance-table__time">{{ $break?->break_in?->format('H:i') ?? '' }}</span>
                    <span class="attendance-table__separator">～</span>
                    <span class="attendance-table__time">{{ $break?->break_out?->format('H:i') ?? '' }}</span>
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
                {{ $attendance?->remarks ?? '' }}
            @endif
        </td>
    </tr>
</table> --}}


<table class="attendance-table">
    @php
        // モデルのメソッドを使って、表示すべきデータ（申請中なら after_data、通常なら実データ）を一括取得
        $displayData = $attendance ? $attendance->getDisplayData($attendanceRequest, $isEditable) : [
            'clock_in' => null,
            'clock_out' => null,
            'remarks' => '',
            'has_pending_request' => false,
        ];

        $breaks = $attendance ? $attendance->getDisplayBreaksModel($attendanceRequest, $isEditable) : collect();
        $maxBreaks = $attendance ? $attendance->getDisplayMaxBreaksModel($attendanceRequest, $isEditable) : 1;

        // 実際に編集フォームとして出すべきかどうかの判定（申請中であれば、編集可能期間であっても入力不可の確認表示にする場合）
        $canInput = $isEditable && !$displayData['has_pending_request'];
    @endphp

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
            @if($canInput)
                <div class="attendance-table__cell-content">
                    <div class="attendance-table__row-wrapper">
                        <input class="attendance-table__input {{ $displayData['clock_in'] ? '' : 'is-empty' }}" type="time" name="clock_in" value="{{ old('clock_in', $displayData['clock_in']?->format('H:i')) }}">
                        <span class="attendance-table__separator">～</span>
                        <input class="attendance-table__input {{ $displayData['clock_out'] ? '' : 'is-empty' }}" type="time" name="clock_out" value="{{ old('clock_out', $displayData['clock_out']?->format('H:i')) }}">
                    </div>
                    @error('clock_in') <div class="error-message">{{ $message }}</div> @enderror
                    @error('clock_out') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            @else
                <span class="attendance-table__time">
                    {{ $displayData['clock_in']?->format('H:i') ?? '' }}
                </span>
                <span class="attendance-table__separator">～</span>
                <span class="attendance-table__time">
                    {{ $displayData['clock_out']?->format('H:i') ?? '' }}
                </span>
            @endif
        </td>
    </tr>

    @for ($i = 0; $i < $maxBreaks; $i++)
        @php
            $break = $breaks[$i] ?? null;
        @endphp

        <tr class="attendance-table__row">
            <th class="attendance-table__header">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
            <td class="attendance-table__cell">
                @if($canInput)
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
                    <span class="attendance-table__time">{{ $break?->break_in?->format('H:i') ?? '' }}</span>
                    <span class="attendance-table__separator">～</span>
                    <span class="attendance-table__time">{{ $break?->break_out?->format('H:i') ?? '' }}</span>
                @endif
            </td>
        </tr>
    @endfor

    <tr class="attendance-table__row">
        <th class="attendance-table__header">備考</th>
        <td class="attendance-table__cell">
            @if($canInput)
                <div class="attendance-table__cell-content">
                    <div class="attendance-table__row-wrapper">
                        <textarea class="attendance-table__textarea" name="remarks">{{ old('remarks', $displayData['remarks']) }}</textarea>
                    </div>
                    @error('remarks') <div class="error-message">{{ $message}}</div> @enderror
                </div>
            @else
                {{ $displayData['remarks'] ?? '' }}
            @endif
        </td>
    </tr>
</table>
