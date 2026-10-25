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
    satisfait: number // 0 ou 1
    operateur_nom: string
    sondage_titre: string
    created_at: string
}

interface OperateurItem {
    id: number
    nom: string
    user_id: number | null
}

interface MessageItem {
    id: number
    sujet: string
    contenu: string
    lu: boolean
    created_at: string
    operateur?: {
        id: number
        nom: string
    }
}

interface AiAnalysis {
    num_samples: number
    features_columns: string[]
    classes: string[]
    predictions: Array<{
        dossier_id: number
        visiteur_nom: string
        visiteur_email: string
        features: number[]
        prob_0_non_satisfait: number
        prob_1_satisfait: number
        total_prob: number
        y_true: number
        y_pred: number
        true_class: string
        pred_class: string
        is_correct: boolean
    }>
    weights_matrix: Array<{
        feature: string
        poids_0_non_satisfait: number
        poids_1_satisfait: number
    }>
    biases: {
        biais_0_non_satisfait: number
        biais_1_satisfait: number
    }
    confusion_matrix: number[][]
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
    history: {
        loss: number[]
        accuracy: number[]
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
    operateurs: OperateurItem[]
    selectedOperateur: OperateurItem | null
    reponses: ReponseItem[]
    summary: {
        total: number
        satisfait_1: number
        insatisfait_0: number
        is_critical: boolean
        color: 'red' | 'green'
        satisfaction_rate: number
    }
    messagesEnvoyes: MessageItem[]
    aiAnalysis: AiAnalysis | null
    operateursStats: Array<{
        id: number
        nom: string
        total_reponses: number
        satisfait_1: number
        insatisfait_0: number
        is_red: boolean
    }>
}>()

const page = usePage()
const showSurveyModal = ref(false)
const showWarningModal = ref(false)
const showAiDetails = ref(false)

// Formulaire pour "Faire un sondage au visiteur"
const surveyForm = useForm({
    operateur_id: props.selectedOperateur?.id ?? props.operateurs[0]?.id ?? 1,
    titre: `Évaluation de la qualité de service — ${props.selectedOperateur?.nom ?? 'Opérateur'}`,
})

// Formulaire pour message d'avertissement
const warningForm = useForm({
    operateur_id: props.selectedOperateur?.id ?? props.operateurs[0]?.id ?? 1,
    sujet: `Avertissement réglementaire ARTEC — Qualité de service dégradée (${props.selectedOperateur?.nom ?? 'Opérateur'})`,
    contenu: `Madame, Monsieur,\n\nL'ARTEC constate que le nombre d'avis négatifs (0) dépasse le seuil réglementaire de tolérance pour votre réseau. Vous êtes mis en demeure de prendre les dispositions nécessaires sous 15 jours.`,
})

function selectOperateur(opId: number) {
    router.get('/artec/dashboard', { operateur_id: opId }, { preserveState: true })
}

function openSurveyModal() {
    surveyForm.operateur_id = props.selectedOperateur?.id ?? props.operateurs[0]?.id ?? 1
    surveyForm.titre = `Évaluation de la qualité de service — ${props.selectedOperateur?.nom ?? 'Opérateur'}`
    showSurveyModal.value = true
}

function submitSurvey() {
    surveyForm.post('/artec/sondages', {
        onSuccess: () => {
            showSurveyModal.value = false
        },
    })
}

function openWarningModal() {
    warningForm.operateur_id = props.selectedOperateur?.id ?? props.operateurs[0]?.id ?? 1
    warningForm.sujet = `Avertissement officiel ARTEC — Seuil d'insatisfaction dépassé (${props.selectedOperateur?.nom ?? 'Opérateur'})`
    warningForm.contenu = `Madame, Monsieur la Direction de ${props.selectedOperateur?.nom},\n\nLe relevé officiel de l'ARTEC indique ${props.summary.insatisfait_0} avis insatisfaits (0) contre seulement ${props.summary.satisfait_1} avis satisfaits (1).\n\nConformément au cahier des charges, nous vous demandons un plan d'amélioration immédiat concernant vos prestations SMS, Voix, Internet et Crédit.`
    showWarningModal.value = true
}

function submitWarning() {
    warningForm.post('/artec/messages', {
        onSuccess: () => {
            showWarningModal.value = false
        },
    })
}

function logout() {
    router.post('/deconnexion')
}

// Couleurs de marque des opérateurs pour les badges
function getOpBadgeClass(nom: string) {
    const n = nom.toLowerCase()
    if (n.includes('yas')) return 'bg-yellow-500/10 text-yellow-300 border-yellow-500/30'
    if (n.includes('airtel')) return 'bg-red-500/10 text-red-300 border-red-500/30'
    return 'bg-orange-500/10 text-orange-300 border-orange-500/30'
}
</script>

<template>
    <Head title="ARTEC — Direction de la Régulation des Télécoms" />

    <div class="artec-app min-h-screen bg-[#070d18] text-slate-100 flex flex-col selection:bg-cyan-500/30">
        <!-- Topbar -->
        <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-600/30 font-black text-white text-lg">
                        📡
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-bold text-white tracking-tight">ARTEC Régulation</h1>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                Organe de Contrôle
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400">Autorité de Régulation des Technologies de Communication</p>
                    </div>
                </div>

                <div class="flex items-center gap-3.5">
                    <button
                        type="button"
                        class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-bold text-xs shadow-lg shadow-cyan-600/20 hover:brightness-110 transition active:scale-95"
                        @click="openSurveyModal"
                    >
                        <span>＋</span>
                        <span>Faire un sondage au visiteur</span>
                    </button>

                    <button
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 hover:bg-red-500/20 text-xs font-semibold transition"
                        @click="logout"
                    >
                        Déconnexion
                    </button>
                </div>
            </div>
        </header>

        <!-- Messages Flash -->
        <div v-if="page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                <span>✓</span>
                <span>{{ page.props.flash.success }}</span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar : Sélection des opérateurs -->
            <aside class="lg:col-span-1 space-y-6">
                <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-xl">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-3">
                        Opérateurs sous régulation
                    </span>

                    <nav class="space-y-2">
                        <button
                            v-for="op in props.operateurs"
                            :key="op.id"
                            type="button"
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border text-left transition"
                            :class="props.selectedOperateur?.id === op.id
                                ? 'bg-slate-800 border-cyan-500/60 shadow-lg shadow-cyan-950/50'
                                : 'bg-slate-900/40 border-slate-800 hover:bg-slate-800/40 text-slate-300'"
                            @click="selectOperateur(op.id)"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="h-3 w-3 rounded-full shrink-0"
                                    :class="op.nom.toLowerCase().includes('yas') ? 'bg-yellow-400 shadow-[0_0_8px_#facc15]' : op.nom.toLowerCase().includes('airtel') ? 'bg-red-500 shadow-[0_0_8px_#ef4444]' : 'bg-orange-500 shadow-[0_0_8px_#f97316]'"
                                ></span>
                                <div>
                                    <strong class="text-xs font-bold text-white block">{{ op.nom }}</strong>
                                    <span class="text-[10px] text-slate-400">Madagascar</span>
                                </div>
                            </div>

                            <!-- Statut voyant rouge / vert selon 0 vs 1 -->
                            <span
                                v-for="stat in props.operateursStats.filter(s => s.id === op.id)"
                                :key="stat.id"
                                class="text-[10px] font-bold px-2 py-0.5 rounded-md"
                                :class="stat.is_red ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'"
                            >
                                {{ stat.is_red ? '0 > 1 (Alerte)' : '1 ≥ 0 (OK)' }}
                            </span>
                        </button>
                    </nav>

                    <div class="mt-6 pt-4 border-t border-slate-800">
                        <button
                            type="button"
                            class="w-full py-2.5 px-3 rounded-xl bg-cyan-600/20 text-cyan-300 border border-cyan-500/30 text-xs font-bold hover:bg-cyan-600/30 transition flex items-center justify-center gap-2"
                            @click="openSurveyModal"
                        >
                            <span>＋</span>
                            <span>Faire un sondage au visiteur</span>
                        </button>
                    </div>
                </div>

                <!-- Carte d'aide & rappel de la règle -->
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/40 p-5 text-xs space-y-3">
                    <div class="font-bold text-white flex items-center gap-2">
                        <span class="text-amber-400">⚖</span> Règle de régulation ARTEC :
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        • Si le nombre de <strong>0</strong> (non satisfait) dépasse le nombre de <strong>1</strong> (satisfait) : résultat en <span class="text-red-400 font-bold">ROUGE</span> ➔ un message d'avertissement est requis.
                    </p>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        • Si le nombre de <strong>1</strong> dépasse ou égale le nombre de <strong>0</strong> : résultat en <span class="text-emerald-400 font-bold">VERT</span> ➔ qualité conforme.
                    </p>
                </div>
            </aside>

            <!-- Contenu central : Synthèse, Tableau à 6 colonnes, IA, Avertissement -->
            <main class="lg:col-span-3 space-y-6">

                <!-- Bannière d'état et résultat COLORÉ ROUGE / VERT -->
                <section
                    class="rounded-3xl border p-6 sm:p-7 transition-all duration-300 shadow-2xl"
                    :class="props.summary.color === 'red'
                        ? 'bg-gradient-to-r from-red-950/50 via-slate-900 to-slate-900 border-red-500/40 shadow-red-950/30'
                        : 'bg-gradient-to-r from-emerald-950/40 via-slate-900 to-slate-900 border-emerald-500/40 shadow-emerald-950/20'"
                >
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="h-3 w-3 rounded-full animate-ping"
                                    :class="props.summary.color === 'red' ? 'bg-red-400' : 'bg-emerald-400'"
                                ></span>
                                <span
                                    class="text-[11px] font-black uppercase tracking-widest"
                                    :class="props.summary.color === 'red' ? 'text-red-400' : 'text-emerald-400'"
                                >
                                    {{ props.summary.color === 'red' ? 'Alerte Qualité — Ratio Critique' : 'Conformité Réglementaire — Qualité Validée' }}
                                </span>
                            </div>

                            <h2 class="text-2xl font-black text-white mt-1">
                                Opérateur : {{ props.selectedOperateur?.nom ?? 'Sélectionné' }}
                            </h2>

                            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                                <span v-if="props.summary.color === 'red'">
                                    ⚠️ Le nombre d'usagers insatisfaits <strong>(0 : {{ props.summary.insatisfait_0 }})</strong> est supérieur aux avis satisfaits <strong>(1 : {{ props.summary.satisfait_1 }})</strong>. Un message d'avertissement doit être adressé à l'opérateur.
                                </span>
                                <span v-else>
                                    ✓ La majorité des usagers sont satisfaits <strong>(1 : {{ props.summary.satisfait_1 }}</strong> vs <strong>0 : {{ props.summary.insatisfait_0 }})</strong>. Taux de satisfaction de {{ props.summary.satisfaction_rate }}%.
                                </span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Bouton d'avertissement immédiat si rouge -->
                            <button
                                v-if="props.summary.color === 'red'"
                                type="button"
                                class="px-5 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs shadow-xl shadow-red-600/30 hover:brightness-110 transition active:scale-95 flex items-center gap-2"
                                @click="openWarningModal"
                            >
                                <span>✉</span>
                                <span>Envoyer l'avertissement officiel</span>
                            </button>

                            <button
                                v-else
                                type="button"
                                class="px-4 py-2.5 rounded-2xl bg-slate-800 border border-slate-700 text-slate-200 font-semibold text-xs hover:bg-slate-700 transition"
                                @click="openWarningModal"
                            >
                                Envoyer une communication
                            </button>
                        </div>
                    </div>

                    <!-- Métriques clés 0 vs 1 -->
                    <div class="grid grid-cols-3 gap-3 mt-6 pt-5 border-t border-slate-800/80">
                        <div class="rounded-2xl bg-slate-900/60 p-3.5 border border-slate-800">
                            <span class="text-[11px] text-slate-400">Total Avis Visiteurs</span>
                            <div class="text-2xl font-black text-white mt-1">{{ props.summary.total }}</div>
                        </div>

                        <div class="rounded-2xl bg-slate-900/60 p-3.5 border border-emerald-500/20">
                            <span class="text-[11px] text-emerald-400 font-bold">Satisfaits (Valeur 1)</span>
                            <div class="text-2xl font-black text-emerald-300 mt-1">{{ props.summary.satisfait_1 }}</div>
                        </div>

                        <div class="rounded-2xl bg-slate-900/60 p-3.5 border border-red-500/20">
                            <span class="text-[11px] text-red-400 font-bold">Non Satisfaits (Valeur 0)</span>
                            <div class="text-2xl font-black text-red-300 mt-1">{{ props.summary.insatisfait_0 }}</div>
                        </div>
                    </div>
                </section>

                <!-- Tableau exact des 6 colonnes demandé -->
                <section class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-white">
                                Tableau des avis usagers — {{ props.selectedOperateur?.nom }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                6 colonnes : Visiteur, SMS, Voix, Internet, Crédit, et Satisfait (binaire 0 ou 1)
                            </p>
                        </div>

                        <span class="text-xs text-slate-400 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
                            {{ props.reponses.length }} entrées enregistrées
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-800 text-[11px] uppercase tracking-wider font-semibold">
                                    <th class="pb-3 px-3">1. Nom Visiteur</th>
                                    <th class="pb-3 px-3 text-center">2. SMS (/10)</th>
                                    <th class="pb-3 px-3 text-center">3. Voix (/10)</th>
                                    <th class="pb-3 px-3 text-center">4. Internet (/10)</th>
                                    <th class="pb-3 px-3 text-center">5. Crédit (/10)</th>
                                    <th class="pb-3 px-3 text-center">6. Satisfait (0 ou 1)</th>
                                    <th class="pb-3 px-3 text-right">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <tr
                                    v-for="r in props.reponses"
                                    :key="r.id"
                                    class="hover:bg-slate-800/30 transition"
                                >
                                    <!-- 1. Nom du visiteur (email sans @gmail.com) -->
                                    <td class="py-3.5 px-3 font-bold text-white flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-full bg-slate-800 flex items-center justify-center text-[10px] text-cyan-300 font-mono">
                                            👤
                                        </span>
                                        <div>
                                            <span class="text-cyan-300">{{ r.visiteur_nom }}</span>
                                            <span class="text-[10px] text-slate-500 block font-mono">{{ r.visiteur_email }}</span>
                                        </div>
                                    </td>

                                    <!-- 2. SMS (note sur 10) -->
                                    <td class="py-3.5 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded-md" :class="r.sms >= 6 ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300'">
                                            {{ r.sms }}/10
                                        </span>
                                    </td>

                                    <!-- 3. Voix (note sur 10) -->
                                    <td class="py-3.5 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded-md" :class="r.voix >= 6 ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300'">
                                            {{ r.voix }}/10
                                        </span>
                                    </td>

                                    <!-- 4. Internet (note sur 10) -->
                                    <td class="py-3.5 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded-md" :class="r.internet >= 6 ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300'">
                                            {{ r.internet }}/10
                                        </span>
                                    </td>

                                    <!-- 5. Crédit (note sur 10) -->
                                    <td class="py-3.5 px-3 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded-md" :class="r.credit >= 6 ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300'">
                                            {{ r.credit }}/10
                                        </span>
                                    </td>

                                    <!-- 6. Satisfait (0 ou 1) -->
                                    <td class="py-3.5 px-3 text-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-black text-xs border"
                                            :class="r.satisfait === 1
                                                ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
                                                : 'bg-red-500/20 text-red-300 border-red-500/30'"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full" :class="r.satisfait === 1 ? 'bg-emerald-400' : 'bg-red-400'"></span>
                                            <span>{{ r.satisfait }} — {{ r.satisfait === 1 ? 'Satisfait' : 'Non satisfait' }}</span>
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-3 text-slate-500 text-right text-[11px] font-mono">
                                        {{ r.created_at }}
                                    </td>
                                </tr>

                                <tr v-if="props.reponses.length === 0">
                                    <td colspan="7" class="py-8 text-center text-slate-500">
                                        Aucune réponse enregistrée pour cet opérateur pour le moment.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Module IA / Machine Learning (modèle binaire 4 critères + 0/1) -->
                <section v-if="props.aiAnalysis" class="rounded-3xl border border-indigo-500/30 bg-slate-900/60 p-6 backdrop-blur-xl space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-base">
                                🧠
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>Classification IA binaire — Satisfaction client</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                        Dense(2, Softmax)
                                    </span>
                                </h3>
                                <p class="text-xs text-slate-400">
                                    Modèle dynamique sur les 4 critères : <em>SMS, Voix, Internet et Crédit</em>, avec sortie <em>0</em> ou <em>1</em> selon le choix du visiteur.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold underline"
                            @click="showAiDetails = !showAiDetails"
                        >
                            {{ showAiDetails ? 'Masquer les détails' : 'Afficher les détails IA' }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            class="rounded-2xl border p-4"
                            :class="props.aiAnalysis.ratio.is_critical ? 'border-red-500/40 bg-red-950/20' : 'border-emerald-500/40 bg-emerald-950/20'"
                        >
                            <span class="text-[10px] uppercase font-bold text-slate-400">Décision ARTEC</span>
                            <div
                                class="text-xl font-black mt-1"
                                :class="props.aiAnalysis.ratio.is_critical ? 'text-red-400' : 'text-emerald-400'"
                            >
                                {{ props.aiAnalysis.ratio.count_1_satisfait >= props.aiAnalysis.ratio.count_0_non_satisfait ? 'Satisfait (1)' : 'Non satisfait (0)' }}
                            </div>
                            <span class="text-xs text-slate-400 mt-1 block">
                                {{ props.aiAnalysis.ratio.count_1_satisfait }} satisfaits vs {{ props.aiAnalysis.ratio.count_0_non_satisfait }} non satisfaits
                            </span>
                        </div>

                        <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-4 space-y-2">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Répartition binaire</span>
                            <div class="space-y-1.5 pt-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-red-400 font-semibold">Non satisfait (0)</span>
                                    <span class="font-mono text-slate-300">{{ props.aiAnalysis.ratio.total > 0 ? ((props.aiAnalysis.ratio.count_0_non_satisfait / props.aiAnalysis.ratio.total) * 100).toFixed(1) : 0 }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div
                                        class="h-full bg-red-500"
                                        :style="{ width: `${props.aiAnalysis.ratio.total > 0 ? (props.aiAnalysis.ratio.count_0_non_satisfait / props.aiAnalysis.ratio.total) * 100 : 0}%` }"
                                    ></div>
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-emerald-400 font-semibold">Satisfait (1)</span>
                                    <span class="font-mono text-slate-300">{{ props.aiAnalysis.ratio.total > 0 ? ((props.aiAnalysis.ratio.count_1_satisfait / props.aiAnalysis.ratio.total) * 100).toFixed(1) : 0 }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div
                                        class="h-full bg-emerald-500"
                                        :style="{ width: `${props.aiAnalysis.ratio.total > 0 ? (props.aiAnalysis.ratio.count_1_satisfait / props.aiAnalysis.ratio.total) * 100 : 0}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="showAiDetails" class="space-y-6 pt-4 border-t border-slate-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                <span class="text-xs font-bold text-indigo-300 mb-3 block">Matrice de confusion (2x2)</span>
                                <table class="w-full text-center text-xs font-mono">
                                    <thead>
                                        <tr class="text-slate-400 border-b border-slate-800 text-[10px]">
                                            <th class="text-left pb-2">Réel \ Prédit</th>
                                            <th class="pb-2 text-red-400">0</th>
                                            <th class="pb-2 text-emerald-400">1</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800">
                                        <tr v-for="(row, i) in props.aiAnalysis.confusion_matrix" :key="i">
                                            <td class="text-left py-2 text-slate-400 font-sans text-[11px]">
                                                {{ i === 0 ? '0 - Non satisfait' : '1 - Satisfait' }}
                                            </td>
                                            <td v-for="(val, j) in row" :key="j" class="py-2" :class="i === j ? 'text-emerald-400 font-bold bg-emerald-500/10 rounded' : 'text-slate-500'">
                                                {{ val }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                <span class="text-xs font-bold text-indigo-300 mb-3 block">Matrice des poids synaptiques (4x2)</span>
                                <table class="w-full text-center text-xs font-mono">
                                    <thead>
                                        <tr class="text-slate-400 border-b border-slate-800 text-[10px]">
                                            <th class="text-left pb-2">Critère</th>
                                            <th class="pb-2 text-red-400">0</th>
                                            <th class="pb-2 text-emerald-400">1</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800">
                                        <tr v-for="w in props.aiAnalysis.weights_matrix" :key="w.feature">
                                            <td class="text-left py-1.5 text-slate-400 font-sans text-[11px]">{{ w.feature }}</td>
                                            <td class="py-1.5 text-slate-300">{{ w.poids_0_non_satisfait }}</td>
                                            <td class="py-1.5 text-slate-300">{{ w.poids_1_satisfait }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4 overflow-x-auto">
                            <span class="text-xs font-bold text-indigo-300 mb-3 block">Données utilisées pour le modèle dynamique</span>
                            <table class="w-full text-left text-xs font-mono">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-800 text-[10px]">
                                        <th class="pb-2">Visiteur</th>
                                        <th class="pb-2">SMS</th>
                                        <th class="pb-2">Voix</th>
                                        <th class="pb-2">Internet</th>
                                        <th class="pb-2">Crédit</th>
                                        <th class="pb-2">Choix</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    <tr v-for="p in props.aiAnalysis.predictions.slice(0, 10)" :key="p.dossier_id">
                                        <td class="py-1.5 text-white">{{ p.visiteur_nom || 'Visiteur' }}</td>
                                        <td class="py-1.5 text-slate-300">{{ p.features[0] }}</td>
                                        <td class="py-1.5 text-slate-300">{{ p.features[1] }}</td>
                                        <td class="py-1.5 text-slate-300">{{ p.features[3] }}</td>
                                        <td class="py-1.5 text-slate-300">{{ p.features[2] }}</td>
                                        <td class="py-1.5" :class="p.y_true === 1 ? 'text-emerald-400' : 'text-red-400'">
                                            {{ p.y_true }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <!-- Modal : "Faire un sondage au visiteur" -->
        <div v-if="showSurveyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            <div class="w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8 shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-white">Faire un sondage aux visiteurs</h3>
                        <p class="text-xs text-slate-400">Lancer une campagne de recueil d'avis sur le réseau</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-white text-lg font-bold p-1" @click="showSurveyModal = false">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitSurvey" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Opérateur concerné</label>
                        <select
                            v-model="surveyForm.operateur_id"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-cyan-500"
                        >
                            <option v-for="op in props.operateurs" :key="op.id" :value="op.id">
                                {{ op.nom }} Madagascar
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Titre de la campagne de sondage</label>
                        <input
                            v-model="surveyForm.titre"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-cyan-500"
                        />
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-300 space-y-1">
                        <span class="font-bold text-cyan-300">Critères évalués par les visiteurs :</span>
                        <p class="text-[11px] text-slate-400">
                            Les usagers noteront les 4 composantes (SMS, Voix, Internet, Crédit sur 10) et indiqueront leur satisfaction binaire (1 ou 0).
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800 transition"
                            @click="showSurveyModal = false"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="surveyForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-cyan-600 text-white font-bold text-xs shadow-lg shadow-cyan-600/30 hover:bg-cyan-500 transition disabled:opacity-50"
                        >
                            {{ surveyForm.processing ? 'Publication...' : 'Diffuser le sondage aux visiteurs' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal : "Envoyer un avertissement à l'opérateur" -->
        <div v-if="showWarningModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            <div class="w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8 shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-red-400">⚠️</span>
                            <span>Avertissement Officiel ARTEC</span>
                        </h3>
                        <p class="text-xs text-slate-400">Notification formelle envoyée à l'opérateur</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-white text-lg font-bold p-1" @click="showWarningModal = false">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitWarning" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Destinataire</label>
                        <select
                            v-model="warningForm.operateur_id"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-red-500"
                        >
                            <option v-for="op in props.operateurs" :key="op.id" :value="op.id">
                                Direction Générale — {{ op.nom }} Madagascar
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sujet de l'injonction</label>
                        <input
                            v-model="warningForm.sujet"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-red-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Motif réglementaire et mise en demeure</label>
                        <textarea
                            v-model="warningForm.contenu"
                            rows="5"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 p-3 text-xs text-white outline-none focus:border-red-500 leading-relaxed font-sans"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800 transition"
                            @click="showWarningModal = false"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="warningForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs shadow-lg shadow-red-600/30 hover:bg-red-500 transition disabled:opacity-50"
                        >
                            {{ warningForm.processing ? 'Envoi...' : 'Transmettre l’avertissement officiel' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
