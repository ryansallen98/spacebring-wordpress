<div class="px-4 py-2 border border-gray-300 rounded-md bg-white mt-4" x-data="synchronization()">
    <h2>Synchronization</h2>
    <p>
        Manage the synchronization settings between your WordPress site and Spacebring.
        Use the options below to configure which data should be synchronized automatically and manually.
    </p>
    <p><em><strong>Note:</strong>
            Ensure that your API credentials are correctly set up in the Credentials tab before configuring
            synchronization settings.
        </em></p>

    <button class="button button-secondary my-2" type="button" @click="runTest" :disabled="status === 'loading'">
        Synchronize Now
    </button>

    <div class="bg-neutral-100 rounded mt-4 mb-2 border border-neutral-300" x-show="status !== 'idle'" x-cloak>
        <div x-data="{ open: false }" class="border-b border-neutral-300">
            <div class="cursor-pointer" @click="open = !open">
                <div class="p-4">
                    <div class="flex flex-row justify-between gap-8 items-center">
                        <div class="flex flex-row gap-8 items-center">
                            <div class="text-sm my-0 font-semibold min-w-20">Status:</div>
                            <div x-show="status !== 'idle'" x-cloak>
                                <div class="flex flex-row items-center gap-2">
                                    <div>
                                        <div x-show="status === 'loading'"
                                            class="size-2 rounded-full border-4 border-gray-300 border-t-blue-500 animate-spin">
                                        </div>
                                        <div x-show="status === 'error'" class="bg-red-500 rounded-full size-2"></div>
                                        <div x-show="status === 'success'" class="bg-green-500 rounded-full size-2">
                                        </div>
                                    </div>

                                    <div>
                                        <span x-show="status === 'loading'">Loading…</span>
                                        <span x-show="status === 'error'">Failed</span>
                                        <span x-show="status === 'success'">Completed</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div :data-open="open"
                            class="w-0 h-0 border-t-6 border-t-transparent border-b-6 border-b-transparent border-l-8 border-l-neutral-500 data-[open='true']:rotate-90 transition-transform">
                        </div>
                    </div>
                </div>
            </div>
            <div :data-open="open" class="overflow-hidden max-h-0 data-[open='true']:max-h-max">
                <div class="flex flex-row gap-8 p-4 bg-neutral-900 text-white">
                    <div class="min-w-20"></div>

                    <div class="flex flex-col gap-2">
                        <template x-for="(log, index) in logs" :key="index">
                            <div><span x-text="log.endpoint"></span><span>: </span><span class="capitalize"
                                    :class="log.status === 'success' ? 'text-green-400' : 'text-red-400'"
                                    x-text="log.status"></span>

                                <pre x-text="JSON.stringify(log.query, null, 2)"></pre>
                            </div>


                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ open: false }">
            <div class="cursor-pointer" @click="open = !open">
                <div class="p-4">
                    <div class="flex flex-row justify-between gap-8 items-center">
                        <div class="flex flex-row gap-8">
                            <div class="text-sm my-0 font-semibold min-w-20">Logs:</div>
                        </div>
                        <div :data-open="open"
                            class="w-0 h-0 border-t-6 border-t-transparent border-b-6 border-b-transparent border-l-8 border-l-neutral-500 data-[open='true']:rotate-90 transition-transform">
                        </div>
                    </div>
                </div>
            </div>
            <div :data-open="open" class="overflow-hidden max-h-0 data-[open='true']:max-h-max">
                <div x-show="logs.length" x-cloak class="max-h-120 overflow-y-auto bg-neutral-900 text-white">
                    <template x-for="log in logs" :key="index">
                        <div class="flex flex-row gap-8 p-4 border-dashed border-white border-b">
                            <div class="min-w-120">
                                <div>
                                    <div><span x-text="log.endpoint"></span><span>: </span><span class="capitalize"
                                            :class="log.status === 'success' ? 'text-green-400' : 'text-red-400'"
                                            x-text="log.status"></span></div>

                                    <pre x-text="JSON.stringify(log.query, null, 2)"></pre>
                                </div>
                            </div>
                            <div class="text-sm">
                                <pre class="mt-2" x-text="JSON.stringify(log.data, null, 2)"></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>
</div>