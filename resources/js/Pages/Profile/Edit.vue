<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import ProfilePictureUpload from '@/Components/ProfilePictureUpload.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { __ } = useTranslations();

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <Head :title="__('client.my_profile')" />
    <AuthenticatedLayout>
        <div class="space-y-6 max-w-3xl mx-auto">

            <!-- Header -->
            <div>
                <h1 class="text-[22px] font-bold text-white tracking-tight">{{ __('client.my_profile') }}</h1>
                <p class="mt-0.5 text-[13px] text-gray-400">{{ __('client.manage_account_settings') }}</p>
            </div>

            <!-- Profile Picture -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6" style="box-shadow:0 0 28px 0 rgba(244,184,64,.05)">
                <h2 class="text-[13px] font-bold text-white uppercase tracking-wider mb-5">{{ __('client.profile_picture') }}</h2>
                <ProfilePictureUpload :user="user" />
            </div>

            <!-- Profile Information -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <h2 class="text-[13px] font-bold text-white uppercase tracking-wider mb-5">{{ __('client.profile_information') }}</h2>
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </div>

            <!-- Password -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <h2 class="text-[13px] font-bold text-white uppercase tracking-wider mb-5">{{ __('client.change_password') }}</h2>
                <UpdatePasswordForm />
            </div>

            <!-- Danger zone -->
            <div class="rounded-2xl border border-red-500/20 bg-red-500/[0.04] p-6">
                <h2 class="text-[13px] font-bold text-red-400 uppercase tracking-wider mb-5">{{ __('client.danger_zone') }}</h2>
                <DeleteUserForm />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
