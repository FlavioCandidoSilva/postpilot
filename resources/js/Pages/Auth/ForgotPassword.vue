<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/api/forgot-password');
};
</script>

<template>

    <Head title="Recuperar Senha - PostPilot" />

    <div class="forgot-container-wrapper">
        <div class="forgot-container">
            <div class="logo">
                <h1>PostPilot</h1>
            </div>

            <h2>Recuperar Senha</h2>

            <p class="info-text">
                Esqueceu sua senha? Sem problemas. Informe seu e-mail abaixo e enviaremos um link para você redefinir
                sua senha.
            </p>

            <div v-if="status" class="success-message">
                {{ status }}
            </div>

            <form @submit.prevent="submit" novalidate>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username" />
                    <InputError :message="form.errors.email" class="error-message" />
                </div>

                <button type="submit" class="button" :disabled="form.processing">
                    Enviar Link de Recuperação
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
body {
    font-family: 'Figtree', sans-serif;
    line-height: 1.6;
    color: #333;
    margin: 0;
    padding: 0;
    background-color: #f8fafc;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}


.forgot-container-wrapper {
    font-family: 'Figtree', sans-serif;
    line-height: 1.6;
    color: #333;
    margin: 0;
    padding: 0;
    background-color: #f8fafc;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.forgot-container {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
}

.logo {
    text-align: center;
    margin-bottom: 2rem;
}

.logo h1 {
    color: #4f46e5;
    margin: 0;
}

h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #1f2937;
}

.info-text {
    font-size: 0.9rem;
    color: #4b5563;
    margin-bottom: 1.5rem;
    text-align: center;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

input[type="email"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-family: inherit;
    font-size: 1rem;
    box-sizing: border-box;
}

.button {
    display: block;
    width: 100%;
    padding: 0.75rem;
    background-color: #4f46e5;
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: 500;
    font-size: 1rem;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s ease;
}

.button:hover:not(:disabled) {
    background-color: #4338ca;
}

.button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.success-message {
    color: #10b981;
    margin-bottom: 1.5rem;
    text-align: center;
}

.error-message {
    color: #ef4444;
    margin-top: 0.5rem;
    text-align: left;
}
</style>
