<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    stats: Object,
    upcomingPosts: Array,
    engagementData: Object,
    subscriptionInfo: Object,
});
</script>

<template>
    <Head title="Dashboard - PostPilot" />

    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12" style="font-family: 'Figtree', sans-serif; line-height: 1.6; color: #333;">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Informações do Plano -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8" style="background-color: #e0e7ff; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 class="text-lg font-semibold mb-4" style="margin-top: 0; color: #4f46e5;">Plano {{ subscriptionInfo?.name || 'Pro' }}</h3>
                    <p class="mb-4">
                        Seu plano atual permite {{ subscriptionInfo?.posts_limit === 'Ilimitado' ? 'postagens ilimitadas' : subscriptionInfo?.posts_limit + ' postagens por mês' }} e acesso a todas as funcionalidades.
                    </p>
                    <p v-if="subscriptionInfo?.expires_at" class="mb-4">
                        Validade: {{ subscriptionInfo.expires_at }}
                    </p>
                    <div v-if="subscriptionInfo?.posts_limit !== 'Ilimitado' && subscriptionInfo?.posts_used !== null" class="mb-4">
                        <p>Postagens utilizadas: {{ subscriptionInfo.posts_used }}/{{ subscriptionInfo.posts_limit }}</p>
                        <div class="w-full bg-gray-200 rounded-full h-2.5" style="height: 10px; background-color: #e5e7eb; border-radius: 5px; margin: 1rem 0;">
                            <div class="bg-blue-600 h-2.5 rounded-full" 
                                 :style="{ width: ((subscriptionInfo.posts_used / parseInt(subscriptionInfo.posts_limit)) * 100) + '%', backgroundColor: '#4f46e5', borderRadius: '5px' }">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <h2 class="text-2xl font-bold mb-6" style="font-size: 1.5rem; margin: 1.5rem 0 1rem; color: #4f46e5;">Estatísticas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center" 
                         style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                        <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Total de Postagens</h3>
                        <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ stats?.total_posts || 42 }}</div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                         style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                        <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Postagens Publicadas</h3>
                        <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ stats?.published_posts || 28 }}</div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                         style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                        <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Postagens Agendadas</h3>
                        <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ stats?.scheduled_posts || 8 }}</div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                         style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                        <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Rascunhos</h3>
                        <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ stats?.draft_posts || 6 }}</div>
                    </div>
                </div>

                <!-- Próximas Postagens -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8" 
                     style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-bottom: 2rem;">
                    <h2 class="text-2xl font-bold mb-6" style="font-size: 1.5rem; margin: 1.5rem 0 1rem; color: #4f46e5;">Próximas Postagens</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" style="width: 100%; border-collapse: collapse;">
                            <thead class="bg-gray-50" style="background-color: #f3f4f6;">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" 
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Plataforma</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="post in upcomingPosts" :key="post.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" 
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb;">{{ post.title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb;">{{ post.scheduled_at }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="{
                                                  'bg-gray-100 text-gray-800': post.status === 'draft',
                                                  'bg-yellow-100 text-yellow-800': post.status === 'ready',
                                                  'bg-green-100 text-green-800': post.status === 'published'
                                              }"
                                              :style="{
                                                  display: 'inline-block',
                                                  padding: '0.25rem 0.5rem',
                                                  borderRadius: '4px',
                                                  fontSize: '0.875rem',
                                                  backgroundColor: post.status === 'draft' ? '#f3f4f6' : post.status === 'ready' ? '#fef3c7' : '#d1fae5',
                                                  color: post.status === 'draft' ? '#4b5563' : post.status === 'ready' ? '#92400e' : '#065f46'
                                              }">
                                            {{ post.status === 'draft' ? 'Rascunho' : post.status === 'ready' ? 'Pronto' : 'Publicado' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb;">{{ post.platform }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dados de Engajamento -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6" 
                     style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-bottom: 2rem;">
                    <h2 class="text-2xl font-bold mb-6" style="font-size: 1.5rem; margin: 1.5rem 0 1rem; color: #4f46e5;">Dados de Engajamento</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                             style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                            <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Curtidas</h3>
                            <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ engagementData?.likes || 1.2 }}k</div>
                        </div>
                        
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                             style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                            <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Comentários</h3>
                            <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ engagementData?.comments || 456 }}</div>
                        </div>
                        
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                             style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                            <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Compartilhamentos</h3>
                            <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ engagementData?.shares || 89 }}</div>
                        </div>
                        
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center"
                             style="background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
                            <h3 class="text-lg font-semibold mb-2" style="margin-top: 0; color: #4f46e5;">Alcance</h3>
                            <div class="text-4xl font-bold text-gray-900" style="font-size: 2.5rem; font-weight: bold; color: #1f2937;">{{ engagementData?.reach || 5.8 }}k</div>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold mb-4" style="font-size: 1.5rem; margin: 1.5rem 0 1rem; color: #4f46e5;">Tendências de Desempenho</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4" 
                         style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; height: 200px; margin-top: 1rem; position: relative; overflow: hidden;">
                        <!-- Gráfico de barras simples -->
                        <div class="flex items-end justify-around h-full">
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 60%;"></div>
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 80%;"></div>
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 45%;"></div>
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 90%;"></div>
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 70%;"></div>
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 85%;"></div>
                            <div class="bg-indigo-600 rounded-t" 
                                 style="width: 8%; background-color: #4f46e5; border-top-left-radius: 4px; border-top-right-radius: 4px; height: 55%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
