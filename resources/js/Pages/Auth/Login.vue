<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-16 text-sm font-medium text-green-600">
            {{ status }}
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
                    autocomplete="current-password"
                />

                <InputError class="mt-8" :message="form.errors.password" />
            </div>

            <div class="mt-16 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-8 text-sm text-gray-600"
                        >次回以降自動でログインする</span
                    >
                </label>
            </div>

            <div class="mt-16 flex items-center justify-end">
                <PrimaryButton
                    class="ms-16"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    ログイン
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
