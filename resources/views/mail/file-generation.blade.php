<x-mail::message>
# Hello {{ $receiverName }}

# {{ $status === 'success' ? '✅ Sales File Generated Successfully' : '❌ Sales File Generation Failed' }}

{{ $messages }} 

</x-mail::message>