<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Manajemen Peran & Hak Akses</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Atur role pengguna dan permission granular di
                pineagrail.com</p>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
            Tambah Role Baru
        </flux:button>
    </div>

    <!-- Flash Alert -->
    @if (session()->has('success'))
        <div
            class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-zinc-800 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-zinc-800 dark:text-red-400 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Bar -->
    <div class="max-w-md">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama role..." icon="magnifying-glass" />
    </div>

    <!-- Tabel Roles -->
    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm text-left text-zinc-600 dark:text-zinc-300">
            <thead class="text-xs uppercase bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                <tr>
                    <th class="px-6 py-3">Nama Role</th>
                    <th class="px-6 py-3">Jumlah Pengguna</th>
                    <th class="px-6 py-3">Permissions</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($roles as $r)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-white capitalize">
                            {{ $r->name }}
                        </td>
                        <td class="px-6 py-4">
                            <flux:badge color="zinc" size="sm">{{ $r->users_count }} Pengguna</flux:badge>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if ($r->name === 'superadmin')
                                    <flux:badge color="purple" size="sm">All Permissions (Full Access)</flux:badge>
                                @else
                                    @forelse($r->permissions as $p)
                                        <flux:badge color="sky" size="sm">{{ $p->name }}</flux:badge>
                                    @empty
                                        <span class="text-xs text-zinc-400">Tidak ada permission</span>
                                    @endforelse
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <flux:button wire:click="openEditModal({{ $r->id }})" size="xs" variant="subtle"
                                icon="pencil-square">
                                Edit
                            </flux:button>

                            @if (!in_array($r->name, ['superadmin', 'admin', 'customer']))
                                <flux:button wire:click="deleteRole({{ $r->id }})"
                                    wire:confirm="Yakin ingin menghapus role ini?" size="xs" variant="danger">
                                    Hapus
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-zinc-500">
                            Tidak ada role yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $roles->links() }}
    </div>

    <!-- MODAL CREATE ROLE -->
    <flux:modal wire:model="showCreateModal" class="md:w-lg space-y-6">
        <div>
            <flux:heading size="lg">Tambah Role Baru</flux:heading>
            <flux:subheading>Buat role baru dan atur hak akses spesifiknya.</flux:subheading>
        </div>

        <form wire:submit="createRole" class="space-y-4">
            <flux:input wire:model="name" label="Nama Role" placeholder="contoh: editor, finance" required />

            <div class="space-y-2">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hak Akses
                    (Permissions)</label>
                <div
                    class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center space-x-2 text-sm cursor-pointer">
                            <input type="checkbox" value="{{ $permission->name }}" wire:model="selectedPermissions"
                                class="rounded border-zinc-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button wire:click="$set('showCreateModal', false)" variant="ghost">Batal</flux:button>
                <flux:button type="submit" variant="primary">Simpan Role</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- MODAL EDIT ROLE -->
    <flux:modal wire:model="showEditModal" class="md:w-lg space-y-6">
        <div>
            <flux:heading size="lg">Edit Role & Permission</flux:heading>
            <flux:subheading>Perbarui nama role dan sesuaikan hak aksesnya.</flux:subheading>
        </div>

        <form wire:submit="updateRole" class="space-y-4">
            <flux:input wire:model="name" label="Nama Role" required />

            <div class="space-y-2">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hak Akses
                    (Permissions)</label>
                <div
                    class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center space-x-2 text-sm cursor-pointer">
                            <input type="checkbox" value="{{ $permission->name }}" wire:model="selectedPermissions"
                                class="rounded border-zinc-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button wire:click="$set('showEditModal', false)" variant="ghost">Batal</flux:button>
                <flux:button type="submit" variant="primary">Perbarui Role</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
