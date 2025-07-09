<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import zxcvbn from 'zxcvbn';
import { ref, watch } from 'vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};


const passwordStrength = ref(0);
const passwordFeedback = ref('');

const strengthLabels = ['Muito fraca', 'Fraca', 'Razoável', 'Forte', 'Muito forte'];
const strengthColors = ['#ef4444', '#f59e42', '#fbbf24', '#22d3ee', '#22c55e'];

watch(() => form.password, (newPassword) => {
  if (newPassword) {
    const result = zxcvbn(newPassword);
    passwordStrength.value = result.score;
    passwordFeedback.value = strengthLabels[result.score];
  } else {
    passwordStrength.value = 0;
    passwordFeedback.value = '';
  }
});
</script>

<template>

  <Head title="Registrar - PostPilot" />

  <div class="register-container-wrapper">
    <div class="register-container">
      <div class="logo">
        <h1>PostPilot</h1>
      </div>

      <h2>Criar Conta</h2>

      <form @submit.prevent="submit" novalidate>
        <div class="form-group">
          <label for="name">Nome</label>
          <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name" />
          <InputError :message="form.errors.name" class="error-message" />
        </div>

        <div class="form-group">
          <label for="email">E-mail</label>
          <input id="email" v-model="form.email" type="email" required autocomplete="username" />
          <InputError :message="form.errors.email" class="error-message" />
        </div>

        <div class="form-group">
          <label for="password">Senha</label>
          <input id="password" v-model="form.password" type="password" required autocomplete="new-password" />
          <InputError :message="form.errors.password" class="error-message" />
        </div>

        <div v-if="form.password" class="password-strength-wrapper">
          <div class="password-strength-bar" :style="{
            width: ((passwordStrength + 1) * 20) + '%',
            backgroundColor: strengthColors[passwordStrength]
          }"></div>
          <div class="password-strength-label" :style="{ color: strengthColors[passwordStrength] }">
            {{ passwordFeedback }}
          </div>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar Senha</label>
          <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
            autocomplete="new-password" />
          <InputError :message="form.errors.password_confirmation" class="error-message" />
        </div>

        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="form-group">
          <label for="terms">
            <div style="display: flex; align-items: center;">
              <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />
              <div style="margin-left: 0.5rem;">
                Concordo com os
                <a target="_blank" :href="route('terms.show')" class="terms-link">
                  Termos de Serviço
                </a>
                e
                <a target="_blank" :href="route('policy.show')" class="terms-link">
                  Política de Privacidade
                </a>
              </div>
            </div>
            <InputError :message="form.errors.terms" class="error-message" />
          </label>
        </div>

        <button type="submit" class="button" :disabled="form.processing"
          @mouseenter="$event.target.style.backgroundColor = '#4338ca'"
          @mouseleave="$event.target.style.backgroundColor = '#4f46e5'">
          Registrar
        </button>
      </form>

      <div class="links">
        <p>
          <Link href="/login" class="link">
          Já tem uma conta? Entrar
          </Link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.register-container-wrapper {
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

.register-container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  width: 100%;
  max-width: 400px;
  margin: 2rem 0;
}

.password-strength-wrapper {
  margin-top: 0.5rem;
}

.password-strength-bar {
  height: 6px;
  border-radius: 3px;
  transition: width 0.3s, background-color 0.3s;
  margin-bottom: 0.3rem;
}

.password-strength-label {
  font-size: 0.9rem;
  font-weight: 500;
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
  color: #333;
}

input[type="text"],
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

.link {
  color: #4f46e5;
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}

.error-message {
  color: #ef4444;
  margin-top: 1rem;
  text-align: center;
  display: none;
}

.error-message[style*='display: block'] {
  display: block;
}

.terms-link {
  color: #4f46e5;
  text-decoration: none;
}

.terms-link:hover {
  text-decoration: underline;
}
</style>
