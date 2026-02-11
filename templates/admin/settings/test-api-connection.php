<div class="px-4 py-2 border border-gray-300 rounded-md bg-white mt-4" x-data="testApiConnection()">
    <h2>Test API Connection</h2>
    <p>Click the button below to test the API connection with the current settings.</p>
    <p><em><strong>Note:</strong> Please save your changes to your authorization before running the test.</em></p>

    <div x-show="status !== 'idle'" x-cloak>
        <div class="my-2 flex-row items-center gap-2 flex">
            <div x-show="status === 'loading'"
                class="size-2 rounded-full border-4 border-gray-300 border-t-blue-500 animate-spin"></div>
            <div x-show="status === 'error'" class="bg-red-500 rounded-full size-2"></div>
            <div x-show="status === 'success'" class="bg-green-500 rounded-full size-2"></div>
            <div>
                <strong>Status:</strong>
                <span x-show="status === 'loading'">Loading…</span>
                <span x-show="status === 'error'">API Connection Failed</span>
                <span x-show="status === 'success'">API Connection Successful</span>
            </div>
        </div>
    </div>

    <button class="button button-secondary my-2" type="button" @click="runTest" :disabled="status === 'loading'">
        Run Test
    </button>

    <div class="bg-neutral-900 p-4 rounded mt-4 mb-2 text-neutral-50 max-h-120 overflow-y-auto" x-show="response" x-cloak>
        <pre x-text="JSON.stringify(response, null, 2)" class="text-neutral-50"></pre>
    </div>
</div>