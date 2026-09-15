@extends('layouts.admin')

@section('title', 'Role & Hak Akses')

@section('content')
    <div x-data="crudModal({
        storeUrl: @js(route('admin.role.store')),
        defaults: { name: '', permissions: [], _protected: false, _superAdmin: false },
        toggleGroup(names) {
            const allSelected = names.every((n) => this.form.permissions.includes(n));
            this.form.permissions = allSelected
                ? this.form.permissions.filter((p) => !names.includes(p))
                : Array.from(new Set([...this.form.permissions, ...names]));
        },
    })">
        <x-ui-page-header title="Role & Hak Akses" description="Buat role kustom dan atur hak akses (permission) tiap role agar lebih fleksibel.">
            <x-slot:action>
                <x-ui-button variant="primary" type="button" x-on:click="openCreate()" icon="plus">Tambah Role</x-ui-button>
            </x-slot:action>
        </x-ui-page-header>

        <div id="crud-table">
            <x-ui-card :padding="''">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Hak Akses</th>
                                <th class="px-6 py-3 text-right">Jumlah User</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($roles as $role)
                                @php
                                    $isProtected = in_array($role->name, $protectedRoles, true);
                                    $isSuperAdmin = $role->name === 'super-admin';
                                    $allPermissionNames = collect($permissionGroups)->flatten()->values();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">
                                        <div class="flex items-center gap-2">
                                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                            @if ($isProtected)
                                                <span title="Role bawaan sistem, nama tidak dapat diubah">
                                                    <x-icon name="info" class="h-4 w-4 text-gray-400" />
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if ($isSuperAdmin)
                                            <x-ui-badge color="amber">Seluruh akses</x-ui-badge>
                                        @elseif ($role->permissions->isEmpty())
                                            <span class="text-gray-400">—</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($role->permissions->take(4) as $permission)
                                                    <x-ui-badge color="indigo">{{ ucwords(str_replace('-', ' ', $permission->name)) }}</x-ui-badge>
                                                @endforeach
                                                @if ($role->permissions->count() > 4)
                                                    <x-ui-badge color="gray">+{{ $role->permissions->count() - 4 }} lainnya</x-ui-badge>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-right text-gray-600">{{ $role->users_count }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $role->id,
                                                    '_updateUrl' => route('admin.role.update', $role),
                                                    'name' => $role->name,
                                                    'permissions' => $isSuperAdmin ? $allPermissionNames : $role->permissions->pluck('name')->values(),
                                                    '_protected' => $isProtected,
                                                    '_superAdmin' => $isSuperAdmin,
                                                ]))"
                                                class="rounded-md p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            @unless ($isProtected)
                                                <button type="button"
                                                    x-on:click="$dispatch('confirm-delete', { url: @js(route('admin.role.destroy', $role)), message: @js('Hapus role \''.$role->name.'\'? Tindakan ini tidak bisa dibatalkan.') })"
                                                    class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui-empty-state :colspan="4" message="Belum ada role." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui-card>
        </div>

        <x-crud-modal create-title="Tambah Role" edit-title="Edit Role" size="3xl">
            <div>
                <x-ui-label for="modal-role-name" required>Nama Role</x-ui-label>
                <div class="mt-2">
                    <x-ui-input name="name" id="modal-role-name" x-model="form.name" x-bind:disabled="form._protected" placeholder="contoh: verifikator-dokumen" />
                </div>
                <p class="mt-1 text-xs text-gray-500" x-show="form._protected" x-cloak>Nama role bawaan sistem tidak dapat diubah, karena dipakai langsung di kode aplikasi.</p>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4" x-show="form._superAdmin" x-cloak>
                <p class="text-sm text-amber-800">Role Super Admin secara otomatis memegang seluruh hak akses dan tidak dapat diubah, agar panitia tidak pernah terkunci dari sistem.</p>
            </div>

            <div class="space-y-5" x-show="!form._superAdmin" x-cloak>
                @foreach ($permissionGroups as $groupLabel => $permissions)
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $groupLabel }}</h3>
                            <button type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-700" x-on:click="toggleGroup(@js(array_values($permissions)))">
                                Pilih semua
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($permissions as $permission)
                                <x-ui-checkbox name="permissions[]" :value="$permission" x-model="form.permissions" :label="ucwords(str_replace('-', ' ', $permission))" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-crud-modal>
    </div>
@endsection
