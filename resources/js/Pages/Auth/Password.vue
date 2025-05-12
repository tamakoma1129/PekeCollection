<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref } from "vue";
import { useToast } from "vue-toast-notification";

defineOptions({
    layout: AuthenticatedLayout,
});

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const $toast = useToast();

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            $toast.success("パスワードを更新しました", {
                position: "top-right",
                duration: 5000,
            });
        },
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <Head title="Password" />

    <div class="bg-white rounded-xl mb-16">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 p-16">
            パスワードの設定
        </h2>
    </div>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-24 sm:px-24 lg:px-40">
            <div class="bg-white p-16 shadow sm:rounded-lg sm:p-40">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            パスワードを更新する
                        </h2>
                    </header>

                    <form
                        @submit.prevent="updatePassword"
                        class="mt-24 space-y-24"
                    >
                        <div>
                            <InputLabel
                                for="current_password"
                                value="現在のパスワード"
                            />

                            <TextInput
                                id="current_password"
                                ref="currentPasswordInput"
                                v-model="form.current_password"
                                type="password"
                                class="mt-4 block w-full"
                                autocomplete="current-password"
                            />

                            <InputError
                                :message="form.errors.current_password"
                                class="mt-8"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="password"
                                value="新しいパスワード"
                            />

                            <TextInput
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="mt-4 block w-full"
                                autocomplete="new-password"
                            />

                            <InputError
                                :message="form.errors.password"
                                class="mt-8"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="password_confirmation"
                                value="パスワード(確認)"
                            />

                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-4 block w-full"
                                autocomplete="new-password"
                            />

                            <InputError
                                :message="form.errors.password_confirmation"
                                class="mt-8"
                            />
                        </div>

                        <div class="flex items-center gap-24">
                            <PrimaryButton :disabled="form.processing"
                                >保存する</PrimaryButton
                            >
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>
