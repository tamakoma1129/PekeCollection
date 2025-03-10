<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-24">
            <p class="text-2xl font-bold text-center mb-">ようこそ!</p>
            <p class="text-center">パスワードを設定して、はじめましょう！</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mt-16">
                <InputLabel for="password" value="パスワード" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-4 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-8" :message="form.errors.password" />
            </div>

            <div class="mt-16">
                <InputLabel
                    for="password_confirmation"
                    value="パスワード(確認)"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-4 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-8"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-16 flex items-center justify-end">
                <PrimaryButton
                    class="ms-16"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    はじめる
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
