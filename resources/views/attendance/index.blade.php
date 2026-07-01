<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Attendance Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('attendance.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    New Entry
                </a>
                <a href="{{ route('admin.attendance.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                    Export Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Total Entries</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                </div>
                @foreach ($designations as $designation)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">{{ $designation }}</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['by_designation'][$designation] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div>
                            <x-input-label for="search" :value="__('Search')" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="$filters['search'] ?? ''" placeholder="Name, email, phone" />
                        </div>
                        <div>
                            <x-input-label for="designation" :value="__('Designation')" />
                            <select id="designation" name="designation" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All</option>
                                @foreach ($designations as $item)
                                    <option value="{{ $item }}" @selected(($filters['designation'] ?? '') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="from" :value="__('From Date')" />
                            <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$filters['from'] ?? ''" />
                        </div>
                        <div>
                            <x-input-label for="to" :value="__('To Date')" />
                            <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$filters['to'] ?? ''" />
                        </div>
                        <div class="flex items-end gap-2">
                            <x-primary-button type="submit">Filter</x-primary-button>
                            <a href="{{ route('admin.attendance.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline pb-2">Reset</a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered At</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($attendances as $attendance)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attendances->firstItem() + $loop->index }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attendance->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attendance->phone }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attendance->email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attendance->displayDesignation() }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $attendance->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No attendance entries yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
