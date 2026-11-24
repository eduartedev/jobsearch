<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
    ollamaModels: {
        type: Array,
        default: () => [],
    },
    claudeModels: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const status = computed(() => page.props.flash?.status ?? page.props.status ?? null);

const form = useForm({
    provider: props.settings.provider,
    ollama_base_url: props.settings.ollama_base_url,
    ollama_model: props.settings.ollama_model ?? '',
    claude_api_key: '',
    claude_model: props.settings.claude_model ?? '',
    document_language: props.settings.document_language,
});

const refreshForm = useForm({});

const submit = () => {
    form.put(route('settings.ai.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset('claude_api_key'),
    });
};

const refreshModels = () => {
    refreshForm.post(route('settings.ai.ollama.refresh'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="IA" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Proveedor de IA
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="status"
                    class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                >
                    {{ status }}
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <p class="mb-6 text-sm text-gray-600">
                        Elige Ollama (local) o Claude (nube). La API key de Claude
                        solo se pide cuando la necesitas y nunca se muestra de
                        nuevo en la UI.
                    </p>

                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel value="Proveedor" />
                            <div class="mt-2 flex gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.provider"
                                        type="radio"
                                        value="ollama"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    Ollama (local)
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.provider"
                                        type="radio"
                                        value="claude"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    Claude (API)
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.provider" />
                        </div>

                        <div>
                            <InputLabel for="document_language" value="Idioma de CV / cartas" />
                            <select
                                id="document_language"
                                v-model="form.document_language"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="es">Español</option>
                                <option value="en">English</option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.document_language"
                            />
                        </div>

                        <div
                            v-if="form.provider === 'ollama'"
                            class="space-y-4 rounded-md border border-gray-200 p-4"
                        >
                            <div>
                                <InputLabel for="ollama_base_url" value="URL de Ollama" />
                                <TextInput
                                    id="ollama_base_url"
                                    v-model="form.ollama_base_url"
                                    type="url"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.ollama_base_url"
                                />
                            </div>

                            <div>
                                <div class="flex items-end justify-between gap-3">
                                    <div class="flex-1">
                                        <InputLabel for="ollama_model" value="Modelo Ollama" />
                                        <select
                                            v-if="ollamaModels.length"
                                            id="ollama_model"
                                            v-model="form.ollama_model"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option disabled value="">Elige un modelo</option>
                                            <option
                                                v-for="model in ollamaModels"
                                                :key="model"
                                                :value="model"
                                            >
                                                {{ model }}
                                            </option>
                                        </select>
                                        <TextInput
                                            v-else
                                            id="ollama_model"
                                            v-model="form.ollama_model"
                                            type="text"
                                            class="mt-1 block w-full"
                                            placeholder="llama3.1"
                                            required
                                        />
                                    </div>
                                    <SecondaryButton
                                        type="button"
                                        :disabled="refreshForm.processing"
                                        @click="refreshModels"
                                    >
                                        Actualizar lista
                                    </SecondaryButton>
                                </div>
                                <p
                                    v-if="!ollamaModels.length"
                                    class="mt-2 text-xs text-amber-700"
                                >
                                    No se detectaron modelos. ¿Ollama está corriendo en esa URL?
                                </p>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.ollama_model"
                                />
                            </div>
                        </div>

                        <div
                            v-else
                            class="space-y-4 rounded-md border border-gray-200 p-4"
                        >
                            <div>
                                <InputLabel for="claude_api_key" value="API key de Claude" />
                                <TextInput
                                    id="claude_api_key"
                                    v-model="form.claude_api_key"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="off"
                                    :placeholder="
                                        settings.has_claude_api_key
                                            ? '•••••••• (deja vacío para conservar la actual)'
                                            : 'sk-ant-...'
                                    "
                                    :required="!settings.has_claude_api_key"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.claude_api_key"
                                />
                            </div>

                            <div>
                                <InputLabel for="claude_model" value="Modelo Claude" />
                                <select
                                    id="claude_model"
                                    v-model="form.claude_model"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option disabled value="">Elige un modelo</option>
                                    <option
                                        v-for="model in claudeModels"
                                        :key="model"
                                        :value="model"
                                    >
                                        {{ model }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.claude_model"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">
                                Guardar
                            </PrimaryButton>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-if="form.recentlySuccessful"
                                    class="text-sm text-gray-600"
                                >
                                    Guardado.
                                </p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
