@php($attachments = $attachments ?? collect())
<ul class="list-group">
    @if($attachments->isNotEmpty())
        @foreach($attachments as $attachment)
            <li class="list-group-item">
                <a href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->filename }}</a>
                @if($attachment->comment)
                    <p class="text-muted mb-0">{{ $attachment->comment }}</p>
                @endif
            </li>
        @endforeach
    @else
        <li class="list-group-item">Нет вложений</li>
    @endif
</ul> 