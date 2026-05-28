<tr>
    <td>{{ $itemView['name'] ?? '-' }}</td>
    @if(!empty($itemView['requires_subject']))
        <td>-</td>
    @endif
    @if(!empty($itemView['show_level_column']))
        <td>{{ $itemView['description'] ?? '-' }}</td>
    @endif
    @forelse(($itemView['table_fields'] ?? []) as $fieldView)
        <td>-</td>
    @empty
        <td>-</td>
    @endforelse
    <td>-</td>
    <td>-</td>
    <td></td>
</tr>
