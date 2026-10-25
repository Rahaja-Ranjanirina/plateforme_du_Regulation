<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
})

const isVisitorMode = ref(false)

function submit() {
    form.post('/connexion')
}

// Comptes rapides pour démonstration / test
const demoAccounts = [
    { label: 'Admin', email: 'admin@gmail.com', pass: 'password', role: 'admin', color: '#6366F1' },
    { label: 'ARTEC', email: 'artec@gmail.com', pass: 'password', role: 'artec', color: '#0EA5E9' },
    { label: 'Yas (Jaune)', email: 'yas@gmail.com', pass: 'password', role: 'operateur', color: '#EAB308' },
    { label: 'Airtel (Rouge)', email: 'airtel@gmail.com', pass: 'password', role: 'operateur', color: '#EF4444' },
    { label: 'Orange', email: 'orange@gmail.com', pass: 'password', role: 'operateur', color: '#F97316' },
    { label: 'Visiteur (Sans mdp)', email: 'rahaja.ranjanirina@gmail.com', pass: '', role: 'visiteur', color: '#10B981' },
]

function fillDemo(acc: typeof demoAccounts[0]) {
    form.email = acc.email
    form.password = acc.pass
    isVisitorMode.value = acc.role === 'visiteur'
}
</script>

<template>
    <Head title="Connexion — Régulation Télécoms" />

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#030712] px-4 py-12 text-slate-100 selection:bg-blue-500/30">
        <!-- Arrière-plan lumineux et immersif -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-32 -top-32 h-[600px] w-[600px] rounded-full bg-blue-600/15 blur-[160px]"></div>
            <div class="absolute -bottom-32 -right-20 h-[650px] w-[650px] rounded-full bg-indigo-600/15 blur-[180px]"></div>
            <div class="absolute left-1/2 top-1/2 h-[400px] w-[400px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-sky-500/10 blur-[140px]"></div>

            <div
                class="absolute inset-0 opacity-[0.07]"
                style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 56px 56px;"
            ></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl">
            <!-- En-tête -->
            <header class="mb-8 flex items-center justify-between">
                <a href="/" class="group flex items-center gap-3.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/15 bg-white/[0.06] shadow-2xl shadow-blue-950/50 backdrop-blur-2xl transition duration-300 group-hover:border-blue-400/40 group-hover:bg-white/[0.1]">
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6 text-blue-300">
                            <path d="M4 18V9M10 18V5M16 18V12M22 18V2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M2 21H22" stroke="currentColor" stroke-opacity=".4" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold tracking-[0.25em] text-white">RÉGULATION</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.3em] text-slate-400">Télécommunications</p>
                    </div>
                </a>

                <div class="hidden items-center gap-2.5 rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 backdrop-blur-2xl sm:flex">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.8)]"></span>
                    <span class="text-xs font-medium text-slate-300">Portail Officiel des 6 Acteurs</span>
                </div>
            </header>

            <!-- Carte principale -->
            <section class="overflow-hidden rounded-[32px] border border-white/[0.12] bg-white/[0.04] shadow-[0_40px_120px_-30px_rgba(0,0,0,0.9)] backdrop-blur-3xl">
                <div class="grid lg:grid-cols-2">

                    <!-- Panneau de gauche -->
                    <div class="relative hidden overflow-hidden border-r border-white/[0.08] p-10 lg:flex lg:min-h-[660px] lg:flex-col lg:justify-between xl:p-14">
                        <div class="relative">
                            <span class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/[0.08] px-3.5 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-blue-200 backdrop-blur-xl">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                Plateforme de Régulation
                            </span>

                            <h1 class="mt-8 max-w-md text-3xl font-normal leading-[1.25] tracking-tight text-white xl:text-4xl">
                                Supervision des
                                <span class="block font-bold bg-gradient-to-r from-blue-200 via-cyan-100 to-indigo-300 bg-clip-text text-transparent">
                                    télécommunications.
                                </span>
                            </h1>

                            <p class="mt-4 max-w-sm text-xs leading-relaxed text-slate-400">
                                Un écosystème connecté regroupant l'Admin, l'ARTEC, les 3 opérateurs (Yas, Airtel, Orange) et les visiteurs du site.
                            </p>
                        </div>

                        <!-- Accès rapide démo -->
                        <div class="my-6 rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-2">
                                <span class="text-amber-400">⚡</span> Connexion rapide (Comptes de test) :
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="acc in demoAccounts"
                                    :key="acc.email"
                                    type="button"
                                    class="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/60 px-3 py-2 text-left text-xs text-slate-200 transition hover:bg-slate-800 hover:border-white/20"
                                    @click="fillDemo(acc)"
                                >
                                    <span class="h-2 w-2 rounded-full shrink-0" :style="{ backgroundColor: acc.color }"></span>
                                    <span class="truncate font-medium">{{ acc.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="relative space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04]">
                                    <span class="text-xs text-blue-300">✓</span>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-200">Visiteurs : Inscription avec e-mail seul</p>
                                    <p class="text-[10px] text-slate-400">Aucun mot de passe requis pour les visiteurs</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panneau de droite : Formulaire de connexion -->
                    <div class="flex items-center justify-center p-8 sm:p-12 lg:p-14">
                        <div class="w-full max-w-md">
                            <div class="mb-6">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-blue-300">
                                    Accès Sécurisé
                                </p>
                                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">
                                    Connexion
                                </h2>
                                <p class="mt-2 text-xs text-slate-400">
                                    Entrez votre adresse email. Pour les visiteurs, le mot de passe n'est pas nécessaire.
                                </p>
                            </div>

                            <!-- Boutons de sélection rapide du type -->
                            <div class="mb-5 flex rounded-xl border border-white/10 bg-slate-950/40 p-1 text-xs">
                                <button
                                    type="button"
                                    class="flex-1 py-2 text-center rounded-lg transition"
                                    :class="!isVisitorMode ? 'bg-blue-600 text-white font-semibold shadow-lg' : 'text-slate-400 hover:text-white'"
                                    @click="isVisitorMode = false"
                                >
                                    Admin / ARTEC / Opérateur
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 py-2 text-center rounded-lg transition"
                                    :class="isVisitorMode ? 'bg-emerald-600 text-white font-semibold shadow-lg' : 'text-slate-400 hover:text-white'"
                                    @click="isVisitorMode = true; form.password = ''"
                                >
                                    Visiteur (Sans mdp)
                                </button>
                            </div>

                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label for="email" class="mb-2 block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Adresse email
                                    </label>
                                    <div class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/50 px-4 transition duration-300 focus-within:border-blue-400/60 focus-within:ring-4 focus-within:ring-blue-500/10">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-slate-500">
                                            <rect x="3" y="5" width="18" height="14" rx="3" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M4 7L12 12L20 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            autocomplete="username"
                                            required
                                            placeholder="ex: admin@gmail.com ou visiteur@gmail.com"
                                            class="min-w-0 flex-1 bg-transparent py-3.5 text-sm text-white outline-none placeholder:text-slate-600"
                                        />
                                    </div>
                                    <p v-if="form.errors.email" class="mt-2 text-xs text-red-400">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div v-if="!isVisitorMode">
                                    <label for="password" class="mb-2 block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Mot de passe
                                    </label>
                                    <div class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/50 px-4 transition duration-300 focus-within:border-blue-400/60 focus-within:ring-4 focus-within:ring-blue-500/10">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-slate-500">
                                            <rect x="5" y="10" width="14" height="11" rx="2.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 10V7A4 4 0 0116 7V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            type="password"
                                            autocomplete="current-password"
                                            placeholder="••••••••••••"
                                            class="min-w-0 flex-1 bg-transparent py-3.5 text-sm text-white outline-none placeholder:text-slate-600"
                                        />
                                    </div>
                                    <p v-if="form.errors.password" class="mt-2 text-xs text-red-400">
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <div v-else class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs text-emerald-300 flex items-center gap-2">
                                    <span>✓</span>
                                    <span>En mode visiteur, votre e-mail suffit pour accéder à votre espace.</span>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="group relative mt-2 flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-xl shadow-blue-950/50 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <span>
                                        {{ form.processing ? 'Connexion en cours...' : 'Accéder à mon espace' }}
                                    </span>
                                    <span class="text-base">→</span>
                                </button>
                            </form>

                            <div class="my-6 flex items-center gap-4">
                                <div class="h-px flex-1 bg-white/[0.08]"></div>
                                <span class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Nouveau Visiteur ?</span>
                                <div class="h-px flex-1 bg-white/[0.08]"></div>
                            </div>

                            <p class="text-center text-xs text-slate-400">
                                Vous êtes un visiteur ?
                                <Link href="/inscription" class="font-semibold text-blue-300 hover:text-cyan-200 hover:underline ml-1">
                                    Créer mon compte en 1 seconde
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>