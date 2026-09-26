<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Manajemen Pengguna</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Kelola akun pengguna, peran, dan hak akses di
                pineagrail.com</p>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
            Tambah Pengguna
        </flux:button>
    </div>

    <!-- Notifikasi Flash -->
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

    <!-- Input Pencarian -->
    <div class="max-w-md">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, atau username..."
            icon="magnifying-glass" />
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
                            {{ $user->username ? '@' . $user->username : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @foreach ($user->roles as $role)
                                @if ($role->name === 'superadmin')
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
                            <flux:button wire:click="openEditModal({{ $user->id }})" size="xs" variant="subtle"
                                icon="pencil-square">
                                Edit
                            </flux:button>

                            <flux:button wire:click="delete({{ $user->id }})"
                                wire:confirm="Yakin ingin menghapus pengguna ini?" size="xs" variant="danger">
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

    <div>
        {{ $users->links() }}
    </div>

    <!-- MODAL CREATE USER -->
    <flux:modal wire:model="showCreateModal" class="md:w-md space-y-6">
        <div>
            <flux:heading size="lg">Tambah Pengguna Baru</flux:heading>
            <flux:subheading>Buat akun baru secara manual untuk sistem pineagrail.com</flux:subheading>
        </div>

        <form wire:submit="createUser" class="space-y-4">
            <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama lengkap" required />
            <flux:input wire:model="email" type="email" label="Email" placeholder="nama@domain.com" required />
            <flux:input wire:model="username" label="Username (Opsional)" placeholder="username" />

            <flux:select wire:model="role" label="Peran (Role)">
                @foreach ($availableRoles as $r)
                    <flux:select.option value="{{ $r->name }}">{{ ucfirst($r->name) }}</flux:select.option>
                @endforeach
            </flux:select>

            <!-- Password Field dengan Toggle Icon Visibility -->
            <flux:input wire:model="password" :type="$showPassword ? 'text' : 'password'" label="Password"
                placeholder="Minimal 8 karakter" required>
                <x-slot name="iconTrailing">
                    <button type="button" wire:click="togglePasswordVisibility"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        @if ($showPassword)
                            <flux:icon name="eye-slash" class="size-4" />
                        @else
                            <flux:icon name="eye" class="size-4" />
                        @endif
                    </button>
                </x-slot>
            </flux:input>

            <!-- Confirm Password Field dengan Toggle Icon Visibility -->
            <flux:input wire:model="password_confirmation" :type="$showPasswordConfirmation ? 'text' : 'password'"
                label="Konfirmasi Password" placeholder="Masukkan ulang password" required>
                <x-slot name="iconTrailing">
                    <button type="button" wire:click="togglePasswordConfirmationVisibility"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        @if ($showPasswordConfirmation)
                            <flux:icon name="eye-slash" class="size-4" />
                        @else
                            <flux:icon name="eye" class="size-4" />
                        @endif
                    </button>
                </x-slot>
            </flux:input>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button wire:click="$set('showCreateModal', false)" variant="ghost">Batal</flux:button>
                <flux:button type="submit" variant="primary">Simpan Pengguna</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- MODAL EDIT USER -->
    <flux:modal wire:model="showEditModal" class="md:w-md space-y-6">
        <div>
            <flux:heading size="lg">Edit Data Pengguna</flux:heading>
            <flux:subheading>Perbarui informasi akun dan hak akses pengguna ini.</flux:subheading>
        </div>

        <form wire:submit="updateUser" class="space-y-4">
            <flux:input wire:model="name" label="Nama Lengkap" required />
            <flux:input wire:model="email" type="email" label="Email" required />
            <flux:input wire:model="username" label="Username (Opsional)" />

            <flux:select wire:model="role" label="Peran (Role)">
                @foreach ($availableRoles as $r)
                    <flux:select.option value="{{ $r->name }}">{{ ucfirst($r->name) }}</flux:select.option>
                @endforeach
            </flux:select>

            <!-- Password Baru (Opsional saat Edit) -->
            <flux:input wire:model="password" :type="$showPassword ? 'text' : 'password'"
                label="Password Baru (Kosongkan jika tidak diubah)">
                <x-slot name="iconTrailing">
                    <button type="button" wire:click="togglePasswordVisibility"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        @if ($showPassword)
                            <flux:icon name="eye-slash" class="size-4" />
                        @else
                            <flux:icon name="eye" class="size-4" />
                        @endif
                    </button>
                </x-slot>
            </flux:input>

            <!-- Konfirmasi Password Baru -->
            <flux:input wire:model="password_confirmation" :type="$showPasswordConfirmation ? 'text' : 'password'"
                label="Konfirmasi Password Baru">
                <x-slot name="iconTrailing">
                    <button type="button" wire:click="togglePasswordConfirmationVisibility"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        @if ($showPasswordConfirmation)
                            <flux:icon name="eye-slash" class="size-4" />
                        @else
                            <flux:icon name="eye" class="size-4" />
                        @endif
                    </button>
                </x-slot>
            </flux:input>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button wire:click="$set('showEditModal', false)" variant="ghost">Batal</flux:button>
                <flux:button type="submit" variant="primary">Perbarui Data</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
