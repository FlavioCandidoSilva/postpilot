<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>

    <Head title="PostPilot - Plataforma de Agendamento de Postagens" />

    <div class="page-container">

        <div class="falling-posts" aria-hidden="true">
            <span class="falling-post" style="--left:10vw; --delay:0s; --duration:10s;">📄</span>
            <span class="falling-post" style="--left:30vw; --delay:2s; --duration:12s;">📄</span>
            <span class="falling-post" style="--left:50vw; --delay:1s; --duration:8s;">📄</span>
            <span class="falling-post" style="--left:70vw; --delay:3s; --duration:14s;">📄</span>
            <span class="falling-post" style="--left:90vw; --delay:4s; --duration:11s;">📄</span>
            <span class="falling-post" style="--left:20vw; --delay:5s; --duration:9s;">📄</span>
            <span class="falling-post" style="--left:40vw; --delay:6s; --duration:13s;">📄</span>
            <span class="falling-post" style="--left:60vw; --delay:1.5s; --duration:10s;">📄</span>
            <span class="falling-post" style="--left:80vw; --delay:0.5s; --duration:15s;">📄</span>
            <span class="falling-post" style="--left:95vw; --delay:7s; --duration:12s;">📄</span>
        </div>

        <!-- Header -->
        <header class="header">
            <div class="container">
                <h1 class="title">PostPilot</h1>
                <p class="subtitle">A plataforma completa para agendamento e geração de conteúdo para LinkedIn</p>
            </div>
        </header>

        <main class="container main-content">
            <!-- Principal -->
            <section class="main-section">
                <h2 class="section-title">Simplifique seu fluxo de trabalho no LinkedIn</h2>
                <p class="section-text">O PostPilot é uma plataforma SaaS completa que ajuda profissionais e empresas a
                    gerenciar, agendar e otimizar suas postagens no LinkedIn, com recursos de geração de conteúdo por
                    IA.</p>
            </section>

            <!-- Recursos -->
            <section class="features">
                <div class="feature-card">
                    <h3 class="feature-title">Agendamento Inteligente</h3>
                    <p>Agende suas postagens nos melhores horários para maximizar o engajamento, com sugestões baseadas
                        em análise de dados.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Geração de Conteúdo com IA</h3>
                    <p>Crie conteúdo profissional com nossa ferramenta de IA integrada, escolhendo entre diferentes tons
                        e formatos.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Organização com Tags</h3>
                    <p>Organize suas ideias e postagens com um sistema completo de tags, status e categorias para fácil
                        acesso.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Calendário Visual</h3>
                    <p>Visualize todas as suas postagens agendadas em um calendário intuitivo, facilitando o
                        planejamento de conteúdo.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Relatórios de Engajamento</h3>
                    <p>Acompanhe o desempenho das suas postagens com relatórios detalhados de engajamento e métricas
                        importantes.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Planos Flexíveis</h3>
                    <p>Escolha entre planos Free e Pro para atender às suas necessidades, com recursos exclusivos para
                        usuários premium.</p>
                </div>
            </section>

            <!-- Call to Action -->
            <div class="cta">
                <h2 class="cta-title">Comece a otimizar suas postagens hoje mesmo!</h2>
                <p class="cta-text">Acesse a versão completa do PostPilot para experimentar todas as funcionalidades.
                </p>
                <Link href="/login" class="cta-button">Acessar o PostPilot</Link>
            </div>

            <!-- Por que escolher -->
            <section class="choose-section">
                <h2 class="section-title">Por que escolher o PostPilot?</h2>
                <p class="section-text">O PostPilot foi desenvolvido para profissionais que desejam manter uma presença
                    consistente e profissional no LinkedIn, economizando tempo e maximizando resultados. Nossa
                    plataforma combina ferramentas de agendamento, organização e criação de conteúdo em uma única
                    solução integrada.</p>
                <p class="section-text">Com o PostPilot, você pode:</p>
                <ul class="feature-list">
                    <li>Planejar seu conteúdo com semanas ou meses de antecedência</li>
                    <li>Gerar ideias e textos profissionais com nossa IA</li>
                    <li>Publicar nos horários de maior engajamento</li>
                    <li>Organizar seu conteúdo com um sistema completo de categorização</li>
                    <li>Analisar o desempenho das suas postagens</li>
                </ul>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 PostPilot. Todos os direitos reservados.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>

.falling-posts {
    pointer-events: none;
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    z-index: 0;
}

.falling-post {
    position: absolute;
    top: -5vh;
    left: var(--left, 50vw);
    font-size: 1.5rem;
    opacity: 0.6;
    user-select: none;
    filter: drop-shadow(0 0 1px rgba(0, 0, 0, 0.2));
    animation-name: fall;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    animation-delay: var(--delay);
    animation-duration: var(--duration);
}

@keyframes fall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 0.6;
    }

    100% {
        transform: translateY(110vh) rotate(360deg);
        opacity: 0;
    }
}

.page-container {
    font-family: 'Figtree', sans-serif;
    line-height: 1.6;
    color: #333;
    margin: 0;
    padding: 0;
    background-color: #f8fafc;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding-left: 1rem;
    padding-right: 1rem;
    box-sizing: border-box;
    width: 100%;
}

.header {
    background-color: #4f46e5;
    color: white;
    padding: 2rem 0;
    text-align: center;
}

.title {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: bold;
}

.subtitle {
    font-size: 1.25rem;
    margin: 0;
}

.main-content {
    padding-top: 2rem;
    padding-bottom: 2rem;
    flex: 1;
}

.main-section {
    margin-bottom: 3rem;
}

.section-title {
    font-size: 1.8rem;
    margin: 2rem 0 1rem 0;
    color: #4f46e5;
    font-weight: bold;
}

.section-text {
    font-size: 1rem;
    margin-bottom: 1rem;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
}

.feature-card {
    background-color: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.feature-card:hover {
  transform: scale(1.05);
  box-shadow: 0 0 5px #4f46e5;
  cursor: default;
}

.feature-card:hover::before {
  opacity: 1;
  transform: rotate(-45deg) translateY(100%);
}

.feature-title {
    color: #4f46e5;
    margin-top: 0;
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: 1.25rem;
}

.cta {
    background-color: #4f46e5;
    color: white;
    text-align: center;
    padding: 3rem 1rem;
    margin: 3rem 0;
    border-radius: 8px;
}

.cta-title {
    font-size: 1.8rem;
    margin: 2rem 0 1rem 0;
    font-weight: bold;
    color: white;
}

.cta-text {
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.cta-button {
    display: inline-block;
    background-color: white;
    color: #4f46e5;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cta-button:hover {
    background-color: #f0f0f0;
    color: #4f46e5;
    transform: scale(1.05);
    border : 1px solid #4f46e5;
}

.choose-section {
    margin-bottom: 3rem;
}

.feature-list {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1rem;
    color: #333;
}

.feature-list li {
    margin-bottom: 0.5rem;
}

.footer {
    background-color: #1f2937;
    color: white;
    padding: 2rem 0;
    text-align: center;
}
</style>
