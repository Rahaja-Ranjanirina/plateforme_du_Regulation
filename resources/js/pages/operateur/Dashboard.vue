<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'

interface ReponseItem {
    id: number
    visiteur_nom: string
    visiteur_email: string
    sms: number
    voix: number
    internet: number
    credit: number
    satisfait: number
    created_at: string
}

interface MessageItem {
    id: number
    sujet: string
    contenu: string
    lu: boolean
    created_at: string
    expediteur?: {
        name: string
    }
}

interface ActualiteItem {
    id: number
    titre: string
    lien: string
    created_at: string
}

interface OffreItem {
    id: number
    titre: string
    description: string
    created_at: string
}

interface AiAnalysis {
    num_samples: number
    classification_report: {
        classes: Array<{
            classe: string
            precision: number
            recall: number
            f1_score: number
            support: number
        }>
        accuracy: number
    }
    ratio: {
        count_0_non_satisfait: number
        count_1_satisfait: number
        total: number
        color: 'red' | 'green'
        is_critical: boolean
        message: string
    }
}

const props = defineProps<{
    operateur?: {
        id?: number
        nom?: string
        user_id?: number
    }
    activeSondage?: {
        id: number
        titre: string
        actif: boolean
    }
    reponses?: ReponseItem[]
    summary?: {
        total: number
        satisfait_1: number
        insatisfait_0: number
        is_critical: boolean
        color: 'red' | 'green'
        satisfaction_rate: number
    }
    messagesArtec?: MessageItem[]
    actualites?: ActualiteItem[]
    offres?: OffreItem[]
    aiAnalysis?: AiAnalysis
}>()

const page = usePage()

const user = computed(() => page.props.auth?.user as unknown as {
    id: number
    name: string
    email: string
    role?: string
    operateur?: {
        id?: number
        nom?: string
    }
} | undefined)

// Modals
const showSurveyModal = ref(false)
const showActualiteModal = ref(false)
const showOffreModal = ref(false)

// Formulaires Inertia
const surveyForm = useForm({
    titre: `Enquête de satisfaction réseau — ${props.operateur?.nom ?? 'Opérateur'}`,
})

const actualiteForm = useForm({
    titre: '',
    lien: '',
})

const offreForm = useForm({
    titre: '',
    description: '',
})

function submitSurvey() {
    surveyForm.post('/operateur/sondages', {
        onSuccess: () => {
            showSurveyModal.value = false
            surveyForm.reset()
        },
    })
}

function submitActualite() {
    actualiteForm.post('/operateur/actualites', {
        onSuccess: () => {
            showActualiteModal.value = false
            actualiteForm.reset()
        },
    })
}

function submitOffre() {
    offreForm.post('/operateur/offres', {
        onSuccess: () => {
            showOffreModal.value = false
            offreForm.reset()
        },
    })
}

function markAsRead(msgId: number) {
    router.post(`/operateur/messages/${msgId}/lu`, {}, { preserveScroll: true })
}

// Couleurs par opérateur
const operatorOverride = ref<string | null>(null)

interface OperatorTheme {
    key: 'yas' | 'airtel' | 'orange'
    name: string
    fullName: string
    initials: string
    primary: string
    secondary: string
    light: string
    border: string
    onPrimary: string
    accentGlow: string
    badgeBg: string
    badgeText: string
    description: string
}

const operatorConfigs: Record<'yas' | 'airtel' | 'orange', OperatorTheme> = {
    yas: {
        key: 'yas',
        name: 'Yas',
        fullName: 'Yas Madagascar',
        initials: 'Y',
        primary: '#EAB308',      // Jaune éclatant (Yellow)
        secondary: '#CA8A04',    // Jaune doré / Ambre soutenu
        light: '#FEFCE8',        // Fond jaune très doux
        border: '#FDE047',
        onPrimary: '#1E1B4B',    // Texte contrasté pour le jaune
        accentGlow: 'rgba(234, 179, 8, 0.45)',
        badgeBg: 'rgba(234, 179, 8, 0.15)',
        badgeText: '#A16207',
        description: 'Votre espace opérateur Yas',
    },
    airtel: {
        key: 'airtel',
        name: 'Airtel',
        fullName: 'Airtel Madagascar',
        initials: 'A',
        primary: '#EF4444',      // Rouge vif (Red)
        secondary: '#B91C1C',    // Rouge foncé
        light: '#FEF2F2',        // Fond rouge très doux
        border: '#FCA5A5',
        onPrimary: '#FFFFFF',
        accentGlow: 'rgba(239, 68, 68, 0.45)',
        badgeBg: 'rgba(239, 68, 68, 0.15)',
        badgeText: '#B91C1C',
        description: 'Votre espace opérateur Airtel',
    },
    orange: {
        key: 'orange',
        name: 'Orange',
        fullName: 'Orange Madagascar',
        initials: 'O',
        primary: '#F97316',      // Orange vif (Orange)
        secondary: '#C2410C',    // Orange profond
        light: '#FFF7ED',        // Fond orange très doux
        border: '#FDBA74',
        onPrimary: '#FFFFFF',
        accentGlow: 'rgba(249, 115, 22, 0.45)',
        badgeBg: 'rgba(249, 115, 22, 0.15)',
        badgeText: '#C2410C',
        description: 'Votre espace opérateur Orange',
    },
}

// Détection automatique de l'opérateur connecté
const detectedOperatorKey = computed<'yas' | 'airtel' | 'orange'>(() => {
    const email = (user.value?.email ?? '').toLowerCase().trim()
    const name = (user.value?.name ?? '').toLowerCase().trim()
    const propNom = (props.operateur?.nom ?? '').toLowerCase().trim()

    if (email.includes('yas') || name.includes('yas') || propNom.includes('yas')) {
        return 'yas'
    }

    if (email.includes('airtel') || name.includes('airtel') || propNom.includes('airtel')) {
        return 'airtel'
    }

    return 'orange'
})

const operator = computed<OperatorTheme>(() => {
    const key = (operatorOverride.value ?? detectedOperatorKey.value) as 'yas' | 'airtel' | 'orange'
    return operatorConfigs[key] ?? operatorConfigs.orange
})

function logout() {
    router.post('/deconnexion')
}

// Navigation par onglets dans le dashboard opérateur
const currentTab = ref<'reponses' | 'messages' | 'actualites' | 'offres'>('reponses')
</script>

<template>
    <Head :title="`Tableau de bord — ${operator.fullName}`" />

    <div
        class="dashboard"
        :class="`theme-${operator.key}`"
        :style="{
            '--primary': operator.primary,
            '--secondary': operator.secondary,
            '--light': operator.light,
            '--border-color': operator.border,
            '--on-primary': operator.onPrimary,
            '--accent-glow': operator.accentGlow,
            '--badge-bg': operator.badgeBg,
            '--badge-text': operator.badgeText,
        }"
    >
        <!-- Sidebar latérale -->
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon">{{ operator.initials }}</div>
                <div>
                    <strong>{{ operator.name }}</strong>
                    <span>ESPACE OPÉRATEUR</span>
                </div>
            </div>

            <div class="sidebar-label">NAVIGATION</div>

            <nav class="navigation">
                <button
                    type="button"
                    class="nav-item"
                    :class="{ 'nav-active': currentTab === 'reponses' }"
                    @click="currentTab = 'reponses'"
                >
                    <span class="nav-icon">▦</span>
                    <span>Avis & Règle 0/1</span>
                    <span v-if="currentTab === 'reponses'" class="active-dot"></span>
                </button>

                <button
                    type="button"
                    class="nav-item"
                    :class="{ 'nav-active': currentTab === 'messages' }"
                    @click="currentTab = 'messages'"
                >
                    <span class="nav-icon">✉</span>
                    <span>Messages ARTEC</span>
                    <span v-if="(props.messagesArtec?.filter(m => !m.lu).length ?? 0) > 0" class="unread-pill">
                        {{ props.messagesArtec?.filter(m => !m.lu).length }}
                    </span>
                    <span v-else-if="currentTab === 'messages'" class="active-dot"></span>
                </button>

                <button
                    type="button"
                    class="nav-item"
                    :class="{ 'nav-active': currentTab === 'actualites' }"
                    @click="currentTab = 'actualites'"
                >
                    <span class="nav-icon">◷</span>
                    <span>Mes Actualités (Liens)</span>
                    <span v-if="currentTab === 'actualites'" class="active-dot"></span>
                </button>

                <button
                    type="button"
                    class="nav-item"
                    :class="{ 'nav-active': currentTab === 'offres' }"
                    @click="currentTab = 'offres'"
                >
                    <span class="nav-icon">◇</span>
                    <span>Mes Offres Commerciales</span>
                    <span v-if="currentTab === 'offres'" class="active-dot"></span>
                </button>
            </nav>

            <div class="sidebar-bottom">
                <div class="help-card">
                    <div class="help-icon">✦</div>
                    <strong>Liaison Automatique ARTEC</strong>
                    <p>Vos sondages sont automatiquement synchronisés avec le régulateur.</p>
                </div>

                <div class="profile">
                    <div class="avatar">{{ operator.initials }}</div>
                    <div class="profile-info">
                        <strong>{{ user?.name ?? operator.name }}</strong>
                        <span class="profile-op-tag">{{ operator.name }}</span>
                    </div>
                    <button class="logout-icon" title="Déconnexion" @click="logout">↗</button>
                </div>
            </div>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="topbar">
                <div class="breadcrumb">
                    <span>Régulation</span>
                    <span class="breadcrumb-separator">/</span>
                    <strong>Tableau de bord {{ operator.name }}</strong>
                </div>

                <div class="topbar-right">
                    <!-- Sélecteur rapide d'opérateur -->
                    <div class="operator-switcher" title="Changer d'opérateur">
                        <span class="switcher-hint">Couleur :</span>
                        <button
                            v-for="(config, key) in operatorConfigs"
                            :key="key"
                            type="button"
                            class="switcher-pill"
                            :class="{ 'switcher-active': operator.key === key }"
                            @click="operatorOverride = key"
                        >
                            <span class="switcher-dot" :style="{ backgroundColor: config.primary }"></span>
                            <span>{{ config.name }}</span>
                        </button>
                        <button
                            v-if="operatorOverride && operatorOverride !== detectedOperatorKey"
                            type="button"
                            class="switcher-reset"
                            title="Rétablir"
                            @click="operatorOverride = null"
                        >
                            ↺
                        </button>
                    </div>

                    <div class="operator-badge-pill">
                        <span class="operator-badge-dot"></span>
                        <span class="operator-badge-name">{{ operator.name }}</span>
                    </div>

                    <div class="topbar-status">
                        <span class="status-dot"></span>
                        <span class="status-text">Opérateur Connecté</span>
                    </div>

                    <div class="top-avatar">{{ operator.initials }}</div>
                </div>
            </header>

            <!-- Notifications Flash -->
            <div v-if="page.props.flash?.success" class="page-content py-2">
                <div class="p-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ page.props.flash.success }}</span>
                </div>
            </div>

            <div class="page-content space-y-6">
                <!-- Bannière d'accueil avec art et dégradé adapté -->
                <section class="welcome-banner">
                    <div class="welcome-text">
                        <span class="eyebrow">
                            <span class="eyebrow-dot"></span>
                            ESPACE OPÉRATEUR AGRÉÉ
                        </span>
                        <h1>Bonjour, {{ user?.name ?? operator.name }} 👋</h1>
                        <p>
                            Bienvenue sur l'espace officiel <strong>{{ operator.fullName }}</strong>.
                            Dès votre connexion, vos sondages sont automatiquement synchronisés avec l'<strong>ARTEC</strong>.
                        </p>
                        <div class="banner-badges">
                            <span>● Synchronisation ARTEC Active</span>
                            <span class="operator-banner-tag">
                                <span class="banner-tag-dot"></span>
                                {{ operator.fullName }}
                            </span>
                        </div>
                    </div>

                    <div class="banner-art" aria-hidden="true">
                        <div class="art-circle circle-one"></div>
                        <div class="art-circle circle-two"></div>
                        <div class="signal">⌁</div>
                        <div class="tower">⌁</div>
                        <div class="art-caption">QUALITÉ & RÉGULATION<br />{{ operator.name.toUpperCase() }}</div>
                    </div>
                </section>

                <!-- Actions d'automatisation et de publication -->
                <section class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <div>
                            <strong class="text-xs text-slate-800 block">Sondage actif envoyé vers les visiteurs & l'ARTEC</strong>
                            <span class="text-[11px] text-slate-500">{{ props.activeSondage?.titre ?? 'Sondage officiel en ligne' }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-md transition"
                            :style="{ backgroundColor: operator.primary, color: operator.onPrimary }"
                            @click="showSurveyModal = true"
                        >
                            ＋ Nouveau Sondage
                        </button>

                        <button
                            type="button"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition"
                            @click="showActualiteModal = true"
                        >
                            ＋ Publier une Actualité (Lien)
                        </button>

                        <button
                            type="button"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 hover:bg-slate-200 transition"
                            @click="showOffreModal = true"
                        >
                            ＋ Ajouter une Offre
                        </button>
                    </div>
                </section>

                <!-- RÉSULTAT ROUGE / VERT POUR L'OPÉRATEUR (0 vs 1) -->
                <section
                    v-if="props.summary"
                    class="rounded-2xl border p-5 transition shadow-sm"
                    :class="props.summary.color === 'red'
                        ? 'bg-red-50 border-red-300 text-red-900'
                        : 'bg-emerald-50 border-emerald-300 text-emerald-900'"
                >
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full" :class="props.summary.color === 'red' ? 'bg-red-600 animate-ping' : 'bg-emerald-600'"></span>
                                <span class="text-[11px] font-black uppercase tracking-wider">
                                    {{ props.summary.color === 'red' ? 'ALERTE : TAUX D’INSATISFACTION ÉLEVÉ (0 > 1)' : 'QUALITÉ CONFORME : SATISFACTION VALIDÉE (1 ≥ 0)' }}
                                </span>
                            </div>
                            <p class="text-xs mt-1 text-slate-700">
                                <span v-if="props.summary.color === 'red'">
                                    Le nombre de notes insatisfaites <strong>(0 : {{ props.summary.insatisfait_0 }})</strong> est supérieur aux avis satisfaits <strong>(1 : {{ props.summary.satisfait_1 }})</strong>. L'ARTEC vous a adressé une mise en demeure.
                                </span>
                                <span v-else>
                                    La majorité des abonnés expriment leur satisfaction <strong>(1 : {{ props.summary.satisfait_1 }} avis)</strong> contre {{ props.summary.insatisfait_0 }} insatisfaits (0).
                                </span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-xs px-3 py-1.5 rounded-xl font-bold font-mono" :class="props.summary.color === 'red' ? 'bg-red-200 text-red-800' : 'bg-emerald-200 text-emerald-800'">
                                Satisfaction : {{ props.summary.satisfaction_rate }}%
                            </span>
                        </div>
                    </div>
                </section>

                <!-- ONGLETS DE CONTENU -->

                <!-- 1. TABLEAU DES AVIS (6 COLONNES) -->
                <section v-if="currentTab === 'reponses'" class="panel space-y-4">
                    <div class="panel-heading">
                        <div>
                            <h2>Tableau des avis usagers — {{ operator.name }}</h2>
                            <p>Les 6 colonnes : Visiteur (email sans @gmail.com), SMS, Voix, Internet, Crédit et Satisfait (0 ou 1)</p>
                        </div>
                        <span class="panel-symbol">▦</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-500 border-b border-slate-200 text-[11px] uppercase tracking-wider font-semibold">
                                    <th class="pb-3 px-3">1. Nom Visiteur</th>
                                    <th class="pb-3 px-3 text-center">2. SMS (/10)</th>
                                    <th class="pb-3 px-3 text-center">3. Voix (/10)</th>
                                    <th class="pb-3 px-3 text-center">4. Internet (/10)</th>
                                    <th class="pb-3 px-3 text-center">5. Crédit (/10)</th>
                                    <th class="pb-3 px-3 text-center">6. Satisfait (0 ou 1)</th>
                                    <th class="pb-3 px-3 text-right">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="r in props.reponses" :key="r.id" class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3 font-bold text-slate-900 flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px]">👤</span>
                                        <span>{{ r.visiteur_nom }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded text-[11px]" :class="r.sms >= 6 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                            {{ r.sms }}/10
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded text-[11px]" :class="r.voix >= 6 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                            {{ r.voix }}/10
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded text-[11px]" :class="r.internet >= 6 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                            {{ r.internet }}/10
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded text-[11px]" :class="r.credit >= 6 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                            {{ r.credit }}/10
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-black text-xs border"
                                            :class="r.satisfait === 1 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-red-100 text-red-800 border-red-300'"
                                        >
                                            <span>{{ r.satisfait }} — {{ r.satisfait === 1 ? 'Satisfait' : 'Non satisfait' }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right text-slate-400 font-mono text-[11px]">{{ r.created_at }}</td>
                                </tr>

                                <tr v-if="(props.reponses?.length ?? 0) === 0">
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        Aucun avis enregistré pour le moment.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 2. MESSAGES ET AVERTISSEMENTS REÇUS DE L'ARTEC -->
                <section v-if="currentTab === 'messages'" class="panel space-y-4">
                    <div class="panel-heading">
                        <div>
                            <h2>Communications et avertissements de l'ARTEC</h2>
                            <p>Courriers de régulation, notifications et retours par rapport aux avis des usagers</p>
                        </div>
                        <span class="panel-symbol">✉</span>
                    </div>

                    <div class="space-y-3">
                        <article
                            v-for="msg in props.messagesArtec"
                            :key="msg.id"
                            class="p-4 rounded-2xl border transition"
                            :class="!msg.lu ? 'bg-red-50/70 border-red-200' : 'bg-slate-50 border-slate-200'"
                        >
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="flex items-center gap-2">
                                    <span v-if="!msg.lu" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white">
                                        NON LU / URGENT
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        LU ✓
                                    </span>
                                    <strong class="text-xs text-slate-900">{{ msg.sujet }}</strong>
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">{{ msg.created_at }}</span>
                            </div>

                            <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed pl-1 border-l-2 border-slate-300">
                                {{ msg.contenu }}
                            </p>

                            <div v-if="!msg.lu" class="mt-3 pt-3 border-t border-slate-200 flex justify-end">
                                <button
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-[11px] font-bold hover:bg-slate-800 transition"
                                    @click="markAsRead(msg.id)"
                                >
                                    Marquer comme lu et accuser réception
                                </button>
                            </div>
                        </article>

                        <div v-if="(props.messagesArtec?.length ?? 0) === 0" class="py-8 text-center text-slate-400">
                            Aucun message de l'ARTEC pour le moment.
                        </div>
                    </div>
                </section>

                <!-- 3. ACTUALITÉS AVEC LIEN CLIQUABLE -->
                <section v-if="currentTab === 'actualites'" class="panel space-y-4">
                    <div class="panel-heading">
                        <div>
                            <h2>Mes Actualités publiées (avec liens externes)</h2>
                            <p>Ces communiqués sont automatiquement affichés dans l'espace visiteur</p>
                        </div>
                        <button
                            type="button"
                            class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs"
                            @click="showActualiteModal = true"
                        >
                            ＋ Nouvelle actualité
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <article
                            v-for="actu in props.actualites"
                            :key="actu.id"
                            class="p-4 rounded-2xl border border-slate-200 bg-slate-50 flex flex-col justify-between space-y-3"
                        >
                            <div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ actu.created_at }}</span>
                                <h3 class="text-sm font-bold text-slate-900 mt-1">{{ actu.titre }}</h3>
                            </div>

                            <a
                                :href="actu.lien"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline"
                            >
                                <span>Lien source de l'article</span>
                                <span>↗</span>
                            </a>
                        </article>

                        <div v-if="(props.actualites?.length ?? 0) === 0" class="col-span-2 py-8 text-center text-slate-400">
                            Aucune actualité publiée pour le moment.
                        </div>
                    </div>
                </section>

                <!-- 4. OFFRES COMMERCIALES -->
                <section v-if="currentTab === 'offres'" class="panel space-y-4">
                    <div class="panel-heading">
                        <div>
                            <h2>Mes Offres commerciales</h2>
                            <p>Gérez les forfaits visibles par les visiteurs de la plateforme</p>
                        </div>
                        <button
                            type="button"
                            class="px-3.5 py-1.5 rounded-xl text-white font-bold text-xs"
                            :style="{ backgroundColor: operator.primary, color: operator.onPrimary }"
                            @click="showOffreModal = true"
                        >
                            ＋ Ajouter une offre
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <article
                            v-for="offre in props.offres"
                            :key="offre.id"
                            class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2"
                        >
                            <span class="text-[10px] text-slate-400 font-mono">{{ offre.created_at }}</span>
                            <h3 class="text-sm font-bold text-slate-900">{{ offre.titre }}</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ offre.description }}</p>
                        </article>

                        <div v-if="(props.offres?.length ?? 0) === 0" class="col-span-2 py-8 text-center text-slate-400">
                            Aucune offre commerciale publiée pour le moment.
                        </div>
                    </div>
                </section>

                <!-- Module IA / Machine Learning Softmax -->
                <section v-if="props.aiAnalysis?.ratio" class="panel space-y-4">
                    <div class="panel-heading">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🧠</span>
                            <div>
                                <h2>Prédiction IA — Risque Réglementaire</h2>
                                <p>Modèle Softmax entraîné sur la satisfaction usager (0 / 1)</p>
                            </div>
                        </div>
                        <span class="role-badge">
                            {{ props.aiAnalysis.ratio.is_critical ? 'Non satisfait (0)' : 'Satisfait (1)' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                        <div>
                            <span class="text-slate-500 font-medium block">Décision retenue</span>
                            <strong class="text-base text-slate-900 block mt-0.5">
                                {{ props.aiAnalysis.ratio.is_critical ? 'Alerte qualité' : 'Qualité conforme' }}
                            </strong>
                            <span class="text-[11px] text-slate-500">
                                Précision du modèle : {{ props.aiAnalysis.classification_report?.accuracy ?? 0 }}%
                            </span>
                        </div>
                        <div class="col-span-2 space-y-1">
                            <span class="text-slate-500 font-medium block">Répartition des avis</span>
                            <div class="flex flex-wrap items-center gap-3 text-slate-700">
                                <span>
                                    Non satisfait (0) :
                                    <strong>{{ props.aiAnalysis.ratio.count_0_non_satisfait }}</strong>
                                    ({{ props.aiAnalysis.ratio.total > 0
                                        ? ((props.aiAnalysis.ratio.count_0_non_satisfait / props.aiAnalysis.ratio.total) * 100).toFixed(1)
                                        : '0.0' }}%)
                                </span>
                                <span>
                                    Satisfait (1) :
                                    <strong>{{ props.aiAnalysis.ratio.count_1_satisfait }}</strong>
                                    ({{ props.aiAnalysis.ratio.total > 0
                                        ? ((props.aiAnalysis.ratio.count_1_satisfait / props.aiAnalysis.ratio.total) * 100).toFixed(1)
                                        : '0.0' }}%)
                                </span>
                            </div>
                            <p class="text-slate-500 text-[11px] italic mt-1">{{ props.aiAnalysis.ratio.message }}</p>
                        </div>
                    </div>
                </section>

                <footer class="footer">
                    <span>© {{ new Date().getFullYear() }} — Plateforme de régulation des télécommunications</span>
                    <span class="footer-brand">Espace officiel {{ operator.name }} Madagascar</span>
                </footer>
            </div>
        </main>

        <!-- MODAL : NOUVEAU SONDAGE -->
        <div v-if="showSurveyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Nouveau sondage auprès des visiteurs</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-700 font-bold" @click="showSurveyModal = false">✕</button>
                </div>
                <form @submit.prevent="submitSurvey" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Titre du sondage</label>
                        <input
                            v-model="surveyForm.titre"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 outline-none"
                        />
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 text-xs text-slate-600" @click="showSurveyModal = false">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900">Diffuser le sondage</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL : NOUVELLE ACTUALITÉ AVEC LIEN -->
        <div v-if="showActualiteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Publier une actualité</h3>
                        <p class="text-[11px] text-slate-500">Ce lien sera affiché dans l'espace visiteurs</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-700 font-bold" @click="showActualiteModal = false">✕</button>
                </div>
                <form @submit.prevent="submitActualite" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Titre de l'article / communiqué</label>
                        <input
                            v-model="actualiteForm.titre"
                            type="text"
                            required
                            placeholder="ex: Extension de la couverture 4G"
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lien Web officiel (URL)</label>
                        <input
                            v-model="actualiteForm.lien"
                            type="url"
                            required
                            placeholder="https://..."
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 outline-none"
                        />
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 text-xs text-slate-600" @click="showActualiteModal = false">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900">Publier l'actualité</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL : NOUVELLE OFFRE -->
        <div v-if="showOffreModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Ajouter une offre commerciale</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-700 font-bold" @click="showOffreModal = false">✕</button>
                </div>
                <form @submit.prevent="submitOffre" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Titre de l'offre</label>
                        <input
                            v-model="offreForm.titre"
                            type="text"
                            required
                            placeholder="ex: Forfait Maxi Data 50Go"
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Description & Tarifs</label>
                        <textarea
                            v-model="offreForm.description"
                            rows="3"
                            required
                            placeholder="Détails du volume, validité et tarifs..."
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 outline-none"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 text-xs text-slate-600" @click="showOffreModal = false">Annuler</button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white"
                            :style="{ backgroundColor: operator.primary, color: operator.onPrimary }"
                        >
                            Enregistrer l'offre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style>
:root {
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    font-synthesis: none;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
}

.dashboard {
    min-height: 100vh;
    color: #172033;
    background: #f5f7fb;
    transition: background-color 0.3s ease;
}

.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 10;
    display: flex;
    flex-direction: column;
    width: 260px;
    padding: 26px 18px 18px;
    color: #d7deec;
    background: #111827;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 9px 30px;
}

.brand-icon,
.account-logo {
    display: grid;
    flex-shrink: 0;
    place-items: center;
    width: 43px;
    height: 43px;
    color: var(--on-primary);
    font-size: 21px;
    font-weight: 850;
    background: linear-gradient(140deg, var(--primary), var(--secondary));
    border-radius: 14px;
    box-shadow: 0 8px 20px color-mix(in srgb, var(--primary) 28%, transparent);
    transition: all 0.3s ease;
}

.brand strong,
.brand span {
    display: block;
}

.brand strong {
    color: white;
    font-size: 17px;
    letter-spacing: -.4px;
}

.brand span {
    margin-top: 4px;
    color: #98a4b8;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 1.5px;
}

.sidebar-label {
    padding: 0 11px;
    margin-bottom: 12px;
    color: #77849b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.3px;
}

.navigation {
    display: grid;
    gap: 7px;
}

.nav-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 13px;
    min-height: 47px;
    padding: 0 12px;
    color: #aeb9ca;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 11px;
    cursor: pointer;
    transition: .2s ease;
    width: 100%;
    text-align: left;
}

.nav-item:hover {
    color: white;
    background: #1d293b;
}

.nav-active {
    color: white;
    background: color-mix(in srgb, var(--primary) 19%, #172033);
    border-color: color-mix(in srgb, var(--primary) 40%, transparent);
}

.nav-icon {
    display: grid;
    place-items: center;
    width: 23px;
    font-size: 19px;
}

.active-dot {
    width: 6px;
    height: 6px;
    margin-left: auto;
    background: var(--primary);
    border-radius: 50%;
    box-shadow: 0 0 8px var(--primary);
    transition: background 0.3s ease;
}

.unread-pill {
    margin-left: auto;
    padding: 2px 7px;
    font-size: 10px;
    font-weight: 800;
    color: white;
    background: #ef4444;
    border-radius: 20px;
}

.sidebar-bottom {
    margin-top: auto;
}

.help-card {
    padding: 16px;
    background: linear-gradient(145deg, #202c40, #192334);
    border: 1px solid #303c50;
    border-radius: 14px;
}

.help-icon {
    display: grid;
    place-items: center;
    width: 29px;
    height: 29px;
    margin-bottom: 12px;
    color: var(--on-primary);
    background: var(--primary);
    border-radius: 9px;
    font-size: 14px;
    font-weight: 800;
    box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 35%, transparent);
    transition: all 0.3s ease;
}

.help-card strong {
    color: white;
    font-size: 12px;
}

.help-card p {
    margin: 7px 0 0;
    color: #a5b1c4;
    font-size: 10px;
    line-height: 1.7;
}

.profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 4px 0;
    margin-top: 18px;
    border-top: 1px solid #2a3546;
}

.avatar,
.top-avatar {
    display: grid;
    flex-shrink: 0;
    place-items: center;
    width: 36px;
    height: 36px;
    color: var(--on-primary);
    font-weight: 800;
    background: linear-gradient(145deg, var(--primary), var(--secondary));
    border-radius: 12px;
    box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 25%, transparent);
    transition: all 0.3s ease;
}

.profile-info {
    display: grid;
    gap: 3px;
    min-width: 0;
    flex: 1;
}

.profile-info strong {
    overflow: hidden;
    color: white;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-op-tag {
    color: var(--primary);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.3s ease;
}

.logout-icon {
    width: 30px;
    height: 30px;
    color: #c5cede;
    background: transparent;
    border: 0;
    border-radius: 8px;
    cursor: pointer;
    transition: all .2s ease;
}

.logout-icon:hover {
    color: white;
    background: #303c50;
}

.main-content {
    min-height: 100vh;
    margin-left: 260px;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 76px;
    padding: 0 38px;
    background: #fff;
    border-bottom: 1px solid #e8ecf3;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 11px;
    color: #8b95a7;
    font-size: 12px;
}

.breadcrumb strong {
    color: #202b3e;
    font-weight: 700;
}

.breadcrumb-separator {
    color: #c3cad5;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.operator-switcher {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 6px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
}

.switcher-hint {
    padding-left: 6px;
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.switcher-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 650;
    color: #475569;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.switcher-pill:hover {
    background: rgba(255, 255, 255, 0.8);
    color: #1e293b;
}

.switcher-pill.switcher-active {
    color: #0f172a;
    background: white;
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    font-weight: 750;
}

.switcher-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

.switcher-reset {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    font-size: 12px;
    color: #64748b;
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
}

.switcher-reset:hover {
    color: #0f172a;
    background: #e2e8f0;
}

.operator-badge-pill {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 750;
    letter-spacing: 0.5px;
    color: var(--secondary);
    background: var(--light);
    border: 1px solid color-mix(in srgb, var(--primary) 35%, transparent);
    border-radius: 20px;
    transition: all 0.3s ease;
}

.operator-badge-dot {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    box-shadow: 0 0 8px var(--primary);
    transition: background 0.3s ease;
}

.topbar-status {
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot,
.stat-indicator {
    width: 7px;
    height: 7px;
    background: #22c55e;
    border-radius: 50%;
}

.status-text {
    color: #637087;
    font-size: 11px;
}

.top-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
}

.page-content {
    width: 100%;
    max-width: 1500px;
    padding: 28px 38px 20px;
    margin: 0 auto;
}

.welcome-banner {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 218px;
    padding: 32px 38px;
    overflow: hidden;
    color: white;
    background:
        radial-gradient(circle at 83% 22%, color-mix(in srgb, var(--primary) 50%, transparent), transparent 35%),
        linear-gradient(115deg, #172238 0%, #202d45 65%, #25354d 100%);
    border: 1px solid color-mix(in srgb, var(--primary) 28%, transparent);
    border-radius: 20px;
    box-shadow: 0 15px 35px rgb(22 34 55 / 9%);
    transition: all 0.3s ease;
}

.welcome-text {
    position: relative;
    z-index: 2;
    max-width: 610px;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--primary);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    transition: color 0.3s ease;
}

.eyebrow-dot {
    width: 6px;
    height: 6px;
    background: var(--primary);
    border-radius: 50%;
    box-shadow: 0 0 6px var(--primary);
}

.welcome-banner h1 {
    margin: 14px 0 9px;
    font-size: clamp(23px, 2.6vw, 31px);
    font-weight: 780;
    letter-spacing: -1.1px;
}

.welcome-banner p {
    max-width: 490px;
    margin: 0;
    color: #c0cbdb;
    font-size: 12px;
    line-height: 1.8;
}

.welcome-banner p strong {
    color: white;
}

.banner-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 20px;
}

.banner-badges span {
    padding: 7px 11px;
    color: #e2e9f3;
    font-size: 9px;
    background: rgb(255 255 255 / 8%);
    border: 1px solid rgb(255 255 255 / 12%);
    border-radius: 30px;
}

.operator-banner-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: white !important;
    font-weight: 700;
    background: color-mix(in srgb, var(--primary) 20%, transparent) !important;
    border-color: color-mix(in srgb, var(--primary) 45%, transparent) !important;
}

.banner-tag-dot {
    width: 6px;
    height: 6px;
    background: var(--primary);
    border-radius: 50%;
    box-shadow: 0 0 6px var(--primary);
}

.banner-art {
    position: relative;
    display: grid;
    flex-shrink: 0;
    place-items: center;
    width: 225px;
    height: 170px;
    margin-left: 15px;
}

.art-circle {
    position: absolute;
    border: 1px solid rgb(255 255 255 / 14%);
    border-radius: 50%;
}

.circle-one {
    width: 165px;
    height: 165px;
}

.circle-two {
    width: 125px;
    height: 125px;
    border-style: dashed;
}

.signal {
    z-index: 1;
    color: var(--primary);
    font-size: 105px;
    font-weight: 200;
    transform: rotate(-25deg);
    text-shadow: 0 0 35px color-mix(in srgb, var(--primary) 65%, transparent);
    transition: all 0.3s ease;
}

.tower {
    position: absolute;
    top: 12px;
    right: 24px;
    color: #fff;
    font-size: 36px;
}

.art-caption {
    position: absolute;
    right: 0;
    bottom: -1px;
    color: #d5deeb;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 2px;
    line-height: 1.8;
    text-align: right;
}

.panel {
    background: white;
    border: 1px solid #e9edf4;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgb(20 34 57 / 2%);
    padding: 24px;
}

.panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.panel-heading h2 {
    margin: 0;
    color: #1d293c;
    font-size: 16px;
    font-weight: 780;
    letter-spacing: -.4px;
}

.panel-heading p {
    margin: 5px 0 0;
    color: #8993a5;
    font-size: 11px;
}

.panel-symbol {
    display: grid;
    flex-shrink: 0;
    place-items: center;
    width: 34px;
    height: 34px;
    color: var(--secondary);
    background: var(--light);
    border: 1px solid color-mix(in srgb, var(--primary) 25%, transparent);
    border-radius: 10px;
}

.role-badge {
    padding: 5px 10px;
    color: var(--secondary) !important;
    background: var(--light);
    border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent);
    border-radius: 20px;
    font-weight: 750;
    font-size: 11px;
}

.footer {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 23px 0 4px;
    color: #929daf;
    font-size: 9px;
    line-height: 1.7;
}

.footer-brand {
    color: var(--secondary);
    font-weight: 700;
    transition: color 0.3s ease;
}

@media (max-width: 1200px) {
    .sidebar { width: 225px; }
    .main-content { margin-left: 225px; }
    .page-content { padding-right: 24px; padding-left: 24px; }
    .topbar { padding: 0 24px; }
}

@media (max-width: 850px) {
    .sidebar { position: static; width: 100%; padding: 15px; }
    .brand { padding-bottom: 15px; }
    .sidebar-label, .sidebar-bottom { display: none; }
    .navigation { display: flex; gap: 5px; overflow-x: auto; padding-bottom: 2px; }
    .nav-item { flex-shrink: 0; min-height: 41px; padding: 0 10px; width: auto; }
    .main-content { margin-left: 0; }
    .topbar { min-height: 60px; flex-wrap: wrap; gap: 10px; }
    .banner-art { width: 150px; opacity: .65; }
}

@media (max-width: 560px) {
    .page-content { padding: 18px 14px; }
    .topbar { padding: 0 15px; }
    .status-text { display: none; }
    .operator-switcher .switcher-hint { display: none; }
    .welcome-banner { min-height: auto; padding: 25px 20px; }
    .welcome-banner h1 { font-size: 24px; }
    .banner-art { position: absolute; right: -45px; opacity: .18; }
    .panel { padding: 17px; }
    .footer { flex-direction: column; }
}
</style>
