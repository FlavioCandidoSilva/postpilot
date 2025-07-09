<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  email: String,
  token: String,
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Recuperar Senha - PostPilot" />

  <div class="forgot-container">
    <div class="logo">
      <h1>PostPilot</h1>
    </div>

    <h2>Recuperar Senha</h2>

    <form @submit.prevent="submit" novalidate>
      <div class="form-group">
        <label for="email">E-mail</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          autofocus
          autocomplete="username"
        />
        <InputError :message="form.errors.email" class="error-message" />
      </div>

      <div class="form-group">
        <label for="password">Senha</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          autocomplete="new-password"
        />
        <InputError :message="form.errors.password" class="error-message" />
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirmar Senha</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          autocomplete="new-password"
        />
        <InputError :message="form.errors.password_confirmation" class="error-message" />
      </div>

      <button
        type="submit"
        class="button"
        :disabled="form.processing"
      >
        Enviar Link de Recuperação
      </button>
    </form>

    <div class="links">
      <p><a href="/login">Voltar para o Login</a></p>
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

.forgot-container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  width: 100%;
  max-width: 400px;
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

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

input[type="email"],
input[type="password"] {
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

.links {
  margin-top: 1.5rem;
  text-align: center;
}

.links a {
  color: #4f46e5;
  text-decoration: none;
}

.links a:hover {
  text-decoration: underline;
}

.error-message {
  color: #ef4444;
  margin-top: 1rem;
  text-align: center;
  display: block;
}
</style>
