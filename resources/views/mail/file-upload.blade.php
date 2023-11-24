<x-mail::message>
    @if ($status === 'success')
        # ✅ Sales File Uploaded Successfully
    @elseif ($status === 'info')
        # ℹ️ Sales File Upload Information
    @else
        # ❌ Sales File Upload Failed
    @endif

    {{ $messages }}
</x-mail::message>