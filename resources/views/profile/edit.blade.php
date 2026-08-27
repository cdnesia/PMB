@php
    $layout = auth()->user()->hasRole('mahasiswa') ? 'layouts.mahasiswa' : 'layouts.admin';
@endphp

@extends($layout)

@section('title', __('nav.profile'))

@section('content')
    <x-ui-page-header :title="__('nav.profile')" :description="__('profile.subtitle')" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui-card>
                @include('profile.partials.update-profile-information-form')
            </x-ui-card>

            <x-ui-card>
                @include('profile.partials.update-password-form')
            </x-ui-card>
        </div>

        <div class="lg:col-span-1">
            <x-ui-card>
                @include('profile.partials.delete-user-form')
            </x-ui-card>
        </div>
    </div>
@endsection
