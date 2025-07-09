<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Entrar - PostPilot" />

  <div class="login-container-wrapper">
    <div class="login-container">
      <div class="logo">
        <h1>PostPilot</h1>
      </div>

      <h2>Entrar</h2>

      <div v-if="status" class="error-message" style="display: block;">
        {{ status }}
      </div>

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
            autocomplete="current-password"
          />
          <InputError :message="form.errors.password" class="error-message" />
        </div>

        <div class="form-group" style="display: flex; align-items: center;">
          <Checkbox v-model:checked="form.remember" name="remember" />
          <label for="remember" style="margin-left: 0.5rem; color: #6b7280; cursor: pointer;">
            Lembrar de mim
          </label>
        </div>

        <button type="submit" class="button" :disabled="form.processing">
          Entrar
        </button>
      </form>

      <div class="links">
        <p v-if="canResetPassword">
          <Link href="/forgot-password">Esqueceu sua senha?</Link>
        </p>
        <p>
          <Link href="/register">Não tem uma conta? Registre-se</Link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Copiado do seu CSS original */

.login-container-wrapper {
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

.login-container {
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
}

.button:hover:not(:disabled) {
  background-color: #4338ca;
}

.button:disabled {
  opacity: 0.6;
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
  display: none;
}

.error-message[style*="display: block"] {
  display: block;
}
</style>
