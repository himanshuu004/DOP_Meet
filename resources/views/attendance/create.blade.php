<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('attendance.store') }}" class="space-y-6" x-data="{ designation: '{{ old('designation') }}' }">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone Number')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="designation" :value="__('Designation')" />
                            <select id="designation" name="designation" x-model="designation" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select designation</option>
                                @foreach ($designations as $item)
                                    <option value="{{ $item }}" @selected(old('designation') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('designation')" />
                        </div>

                        <div x-show="designation === 'Other'" x-cloak>
                            <x-input-label for="designation_other" :value="__('Please specify (optional)')" />
                            <x-text-input id="designation_other" name="designation_other" type="text" class="mt-1 block w-full" :value="old('designation_other')" />
                            <x-input-error class="mt-2" :messages="$errors->get('designation_other')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Entry') }}</x-primary-button>
                            <a href="{{ route('admin.attendance.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">View Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
