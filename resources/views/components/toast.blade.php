@if(session('success') || session('error'))
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => show = false, 4000)"
     class="fixed bottom-4 right-4 z-50 flex items-center p-4 mb-4 text-white rounded-lg shadow-xl border-l-4 transition-all duration-300"
     :class="{ 'bg-green-800 border-green-500': '{{ session('success') }}', 'bg-red-800 border-red-500': '{{ session('error') }}' }"
     role="alert">
    
    @if(session('success'))
        <svg class="flex-shrink-0 w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="font-medium text-lg">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <svg class="flex-shrink-0 w-6 h-6 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="font-medium text-lg">{{ session('error') }}</div>
    @endif

    <button @click="show = false" type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex h-8 w-8 text-gray-300 hover:text-white" aria-label="Close">
        <span class="sr-only">Close</span>
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
    </button>
</div>
@endif
