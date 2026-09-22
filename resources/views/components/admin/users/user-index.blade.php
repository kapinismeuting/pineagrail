<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Manajemen Pengguna</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Kelola akun pengguna, peran, dan hak akses di pineagrail.com</p>
        </div>
    </div>

    <!-- Notifikasi Flash -->
    @if (session()->has('success'))
        <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-zinc-800 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-zinc-800 dark:text-red-400 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Input Pencarian -->
    <div class="max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama, email, atau username..."
            icon="magnifying-glass"
        />
    </div>

    <!-- Tabel Pengguna -->
    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm text-left text-zinc-600 dark:text-zinc-300">
            <thead class="text-xs uppercase bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                <tr>
                    <th class="px-6 py-3">Pengguna</th>
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Tanggal Daftar</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($users as $user)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                            <div>
                                <div class="font-semibold">{{ $user->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $user->email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->username ? '@'.$user->username : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @foreach ($user->roles as $role)
                                @if($role->name === 'superadmin')
                                    <flux:badge color="violet" size="sm">Superadmin</flux:badge>
                                @elseif($role->name === 'admin')
                                    <flux:badge color="sky" size="sm">Admin</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Customer</flux:badge>
                                @endif
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <!-- Tombol Promote / Demote -->
                            @if(!$user->hasRole('superadmin'))
                                <flux:button
                                    wire:click="promote({{ $user->id }})"
                                    wire:confirm="Yakin ingin menaikkan user ini menjadi Superadmin?"
                                    size="xs"
                                    variant="filled"
                                >
                                    Promote
                                </flux:button>
                            @else
                                <flux:button
                                    wire:click="demote({{ $user->id }}, 'admin')"
                                    wire:confirm="Yakin ingin menurunkan peran Superadmin ini menjadi Admin?"
                                    size="xs"
                                    variant="subtle"
                                >
                                    Demote
                                </flux:button>
                            @endif

                            <!-- Tombol Hapus -->
                            <flux:button
                                wire:click="delete({{ $user->id }})"
                                wire:confirm="Yakin ingin menghapus pengguna ini?"
                                size="xs"
                                variant="danger"
                            >
                                Hapus
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-zinc-500">
                            Tidak ada pengguna yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $users->links() }}
    </div>
</div>
