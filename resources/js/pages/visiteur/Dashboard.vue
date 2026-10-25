<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'

interface SondageItem {
    id: number
    titre: string
    operateur_id: number
    operateur_nom: string
    auteur_nom: string
    auteur_role: string
    a_repondu: boolean
    ma_reponse: {
        sms: number
        voix: number
        internet: number
        credit: number
        satisfait: number
        created_at: string
    } | null
}

interface OffreItem {
    id: number
    titre: string
    description: string
    operateur_nom: string
    created_at: string
}

interface ActualiteItem {
    id: number
    titre: string
    lien: string
    operateur_nom: string
    created_at: string
}

const props = defineProps<{
    sondages: SondageItem[]
    offres: OffreItem[]
    actualites: ActualiteItem[]
    visiteurName: string
}>()

const page = usePage()
const activeSection = ref<'sondages' | 'offres' | 'actualites'>('sondages')
const activeSondageId = ref<number | null>(props.sondages[0]?.id ?? null)

// Formulaire pour répondre au sondage
const reponseForm = useForm({
    sondage_id: props.sondages[0]?.id ?? 1,
    sms: 7,
    voix: 8,
    internet: 7,
    credit: 7,
    satisfait: '1', // '1' ou '0'
})

function selectSondage(sondage: SondageItem) {
    activeSondageId.value = sondage.id
    reponseForm.sondage_id = sondage.id
    if (sondage.ma_reponse) {
        reponseForm.sms = sondage.ma_reponse.sms
        reponseForm.voix = sondage.ma_reponse.voix
        reponseForm.internet = sondage.ma_reponse.internet
        reponseForm.credit = sondage.ma_reponse.credit
        reponseForm.satisfait = String(sondage.ma_reponse.satisfait)
    }
}

function submitReponse() {
    reponseForm.post('/visiteur/reponses', {
        preserveScroll: true,
    })
}

function logout() {
    router.post('/deconnexion')
}

function getOpBadge(nom: string) {
    const n = nom.toLowerCase()
    if (n.includes('yas')) return 'bg-yellow-500/10 text-yellow-300 border-yellow-500/30'
    if (n.includes('airtel')) return 'bg-red-500/10 text-red-300 border-red-500/30'
    return 'bg-orange-500/10 text-orange-300 border-orange-500/30'
}
</script>

<template>
    <Head title="Espace Usager & Visiteur — Régulation Télécoms" />

    <div class="visiteur-app min-h-screen bg-[#070e1b] text-slate-100 flex flex-col selection:bg-emerald-500/30">
        <!-- Topbar -->
        <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-600/30 font-black text-white text-lg">
                        ✨
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-bold text-white tracking-tight">Espace Visiteur & Usager</h1>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                Voix Citoyenne
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400">Consultation des offres, actualités et évaluation des réseaux</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200">
                        <span class="text-emerald-400 font-bold">@</span>
                        <span class="font-bold text-white">{{ props.visiteurName }}</span>
                    </div>

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

        <!-- Flash alert -->
        <div v-if="page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                <span>✓</span>
                <span>{{ page.props.flash.success }}</span>
            </div>
        </div>

        <!-- Corps -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
            <!-- Bannière d'accueil -->
            <section class="rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950/30 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xl">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-400">Participez à la régulation</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">
                        Bienvenue, {{ props.visiteurName }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-2 max-w-2xl leading-relaxed">
                        Évaluez la qualité de service de votre opérateur (SMS, Voix, Internet, Crédit et satisfaction). Vos avis sont transmis directement à l'<strong>ARTEC</strong> et pris en compte pour la régulation des télécoms à Madagascar.
                    </p>
                </div>

                <div class="flex items-center gap-2 bg-slate-800/60 p-1.5 rounded-2xl border border-slate-700/60 shrink-0">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition"
                        :class="activeSection === 'sondages' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-white'"
                        @click="activeSection = 'sondages'"
                    >
                        Sondages ({{ props.sondages.length }})
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition"
                        :class="activeSection === 'offres' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-white'"
                        @click="activeSection = 'offres'"
                    >
                        Offres Opérateurs ({{ props.offres.length }})
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition"
                        :class="activeSection === 'actualites' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-white'"
                        @click="activeSection = 'actualites'"
                    >
                        Actualités ({{ props.actualites.length }})
                    </button>
                </div>
            </section>

            <!-- SECTION 1 : SONDAGES & ÉVALUATION (SMS, VOIX, INTERNET, CRÉDIT, SATISFAIT) -->
            <section v-if="activeSection === 'sondages'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Colonne gauche : Liste des sondages -->
                <div class="space-y-4 lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Sondages disponibles
                        </span>
                        <span class="text-[11px] text-emerald-400">Enquêtes officielles</span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="s in props.sondages"
                            :key="s.id"
                            class="p-4 rounded-2xl border cursor-pointer transition text-left"
                            :class="activeSondageId === s.id
                                ? 'bg-slate-800/90 border-emerald-500/60 shadow-xl shadow-emerald-950/40'
                                : 'bg-slate-900/60 border-slate-800 hover:bg-slate-800/40'"
                            @click="selectSondage(s)"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="getOpBadge(s.operateur_nom)">
                                    {{ s.operateur_nom }}
                                </span>

                                <span v-if="s.a_repondu" class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                                    ✓ Avis soumis
                                </span>
                                <span v-else class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                                    À renseigner
                                </span>
                            </div>

                            <h3 class="text-sm font-bold text-white leading-snug">{{ s.titre }}</h3>

                            <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Émetteur : {{ s.auteur_role === 'artec' ? 'ARTEC Régulation' : s.operateur_nom }}</span>
                                <span class="text-emerald-400 font-semibold">Participer →</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite : Formulaire de notation (SMS, Voix, Internet, Crédit, Satisfait 0 ou 1) -->
                <div class="lg:col-span-2">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6 sm:p-8 backdrop-blur-xl shadow-2xl space-y-6">
                        <div class="border-b border-slate-800 pb-5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Questionnaire de Qualité</span>
                            <h2 class="text-xl font-bold text-white mt-1">
                                Donnez votre avis sur le réseau
                            </h2>
                            <p class="text-xs text-slate-400 mt-1">
                                Notez chaque composante sur 10, puis confirmez votre satisfaction globale (1 = Satisfait, 0 = Non satisfait).
                            </p>
                        </div>

                        <!-- État déjà répondu -->
                        <div v-if="props.sondages.find(s => s.id === activeSondageId)?.a_repondu" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span>✓</span>
                                <span>Vous avez déjà répondu à cette enquête. Vous pouvez ajuster vos notes ci-dessous si nécessaire.</span>
                            </div>
                        </div>

                        <form @submit.prevent="submitReponse" class="space-y-6">
                            <!-- 1. SMS -->
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-white flex items-center gap-2">
                                        <span class="text-base">💬</span>
                                        <span>Qualité des SMS (Délai d'envoi & Réception) :</span>
                                    </label>
                                    <span class="text-sm font-black text-cyan-400 font-mono px-2.5 py-1 rounded-lg bg-cyan-500/10 border border-cyan-500/20">
                                        {{ reponseForm.sms }} / 10
                                    </span>
                                </div>
                                <input
                                    v-model.number="reponseForm.sms"
                                    type="range"
                                    min="0"
                                    max="10"
                                    step="1"
                                    class="w-full accent-cyan-500 cursor-pointer"
                                />
                                <div class="flex justify-between text-[10px] text-slate-500">
                                    <span>0 (Médiocre / Échecs récurrents)</span>
                                    <span>5 (Moyen)</span>
                                    <span>10 (Instantané et Parfait)</span>
                                </div>
                            </div>

                            <!-- 2. Voix -->
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-white flex items-center gap-2">
                                        <span class="text-base">📞</span>
                                        <span>Qualité de la Voix & Appels (Clarté, coupures) :</span>
                                    </label>
                                    <span class="text-sm font-black text-blue-400 font-mono px-2.5 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20">
                                        {{ reponseForm.voix }} / 10
                                    </span>
                                </div>
                                <input
                                    v-model.number="reponseForm.voix"
                                    type="range"
                                    min="0"
                                    max="10"
                                    step="1"
                                    class="w-full accent-blue-500 cursor-pointer"
                                />
                                <div class="flex justify-between text-[10px] text-slate-500">
                                    <span>0 (Inaudible / Coupures fréquentes)</span>
                                    <span>5 (Moyen)</span>
                                    <span>10 (Son HD cristal)</span>
                                </div>
                            </div>

                            <!-- 3. Internet -->
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-white flex items-center gap-2">
                                        <span class="text-base">🌐</span>
                                        <span>Qualité d'Internet (Vitesse 4G/Fibre, Stabilité) :</span>
                                    </label>
                                    <span class="text-sm font-black text-emerald-400 font-mono px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                        {{ reponseForm.internet }} / 10
                                    </span>
                                </div>
                                <input
                                    v-model.number="reponseForm.internet"
                                    type="range"
                                    min="0"
                                    max="10"
                                    step="1"
                                    class="w-full accent-emerald-500 cursor-pointer"
                                />
                                <div class="flex justify-between text-[10px] text-slate-500">
                                    <span>0 (Extrêmement lent / Déconnexions)</span>
                                    <span>5 (Correct)</span>
                                    <span>10 (Très haut débit sans latence)</span>
                                </div>
                            </div>

                            <!-- 4. Crédit -->
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-white flex items-center gap-2">
                                        <span class="text-base">💳</span>
                                        <span>Tarifs & Gestion du Crédit (Rapport qualité/prix) :</span>
                                    </label>
                                    <span class="text-sm font-black text-amber-400 font-mono px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                                        {{ reponseForm.credit }} / 10
                                    </span>
                                </div>
                                <input
                                    v-model.number="reponseForm.credit"
                                    type="range"
                                    min="0"
                                    max="10"
                                    step="1"
                                    class="w-full accent-amber-500 cursor-pointer"
                                />
                                <div class="flex justify-between text-[10px] text-slate-500">
                                    <span>0 (Trop cher / Débits inexpliqués)</span>
                                    <span>5 (Abordable)</span>
                                    <span>10 (Tarifs justes et transparents)</span>
                                </div>
                            </div>

                            <!-- 5. Satisfait (0 ou 1) : Spécification essentielle de l'utilisateur -->
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-xs font-bold text-white block">
                                            Êtes-vous globalement satisfait de cet opérateur ?
                                        </label>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            La colonne finale requiert une valeur binaire : <strong>0</strong> (Non satisfait) ou <strong>1</strong> (Satisfait).
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-black border"
                                        :class="reponseForm.satisfait === '1' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-red-500/20 text-red-300 border-red-500/40'"
                                    >
                                        {{ reponseForm.satisfait === '1' ? '1 (Satisfait)' : '0 (Non satisfait)' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <label
                                        class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                                        :class="reponseForm.satisfait === '1'
                                            ? 'bg-emerald-500/15 border-emerald-500/50 text-emerald-300 font-bold'
                                            : 'bg-slate-800/40 border-slate-700 text-slate-300'"
                                    >
                                        <input
                                            v-model="reponseForm.satisfait"
                                            type="radio"
                                            value="1"
                                            class="accent-emerald-500"
                                        />
                                        <div>
                                            <span class="block text-xs font-bold">1 — Oui, je suis satisfait</span>
                                            <span class="text-[10px] text-slate-400 block font-normal">Prestations convenables</span>
                                        </div>
                                    </label>

                                    <label
                                        class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                                        :class="reponseForm.satisfait === '0'
                                            ? 'bg-red-500/15 border-red-500/50 text-red-300 font-bold'
                                            : 'bg-slate-800/40 border-slate-700 text-slate-300'"
                                    >
                                        <input
                                            v-model="reponseForm.satisfait"
                                            type="radio"
                                            value="0"
                                            class="accent-red-500"
                                        />
                                        <div>
                                            <span class="block text-xs font-bold">0 — Non, je ne suis pas satisfait</span>
                                            <span class="text-[10px] text-slate-400 block font-normal">Service décevant</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="reponseForm.processing"
                                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-sm shadow-xl shadow-emerald-600/30 hover:brightness-110 transition active:scale-95 disabled:opacity-50"
                            >
                                {{ reponseForm.processing ? 'Enregistrement de votre avis...' : 'Transmettre mon évaluation officielle' }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- SECTION 2 : OFFRES PUBLIÉES PAR LES 3 OPÉRATEURS -->
            <section v-if="activeSection === 'offres'" class="space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-white">Offres commerciales des opérateurs</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Découvrez les forfaits, packs internet et offres promotionnelles publiées par Yas, Airtel et Orange.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article
                        v-for="offre in props.offres"
                        :key="offre.id"
                        class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl flex flex-col justify-between hover:border-slate-700 transition space-y-4"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border" :class="getOpBadge(offre.operateur_nom)">
                                    {{ offre.operateur_nom }}
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ offre.created_at }}</span>
                            </div>

                            <h4 class="text-base font-bold text-white">{{ offre.titre }}</h4>
                            <p class="text-xs text-slate-300 leading-relaxed">{{ offre.description }}</p>
                        </div>

                        <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                            <span>Réseau : {{ offre.operateur_nom }} Madagascar</span>
                            <span class="text-emerald-400 font-semibold">Offre vérifiée ✓</span>
                        </div>
                    </article>
                </div>
            </section>

            <!-- SECTION 3 : ACTUALITÉS AVEC LIEN CLIQUABLE -->
            <section v-if="activeSection === 'actualites'" class="space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-white">Actualités et communiqués officiels</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Articles et annonces publiés par les opérateurs (avec lien externe cliquable).
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article
                        v-for="actu in props.actualites"
                        :key="actu.id"
                        class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl flex flex-col justify-between hover:border-slate-700 transition space-y-4"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border" :class="getOpBadge(actu.operateur_nom)">
                                    {{ actu.operateur_nom }}
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ actu.created_at }}</span>
                            </div>

                            <h4 class="text-base font-bold text-white">{{ actu.titre }}</h4>
                        </div>

                        <div class="pt-4 border-t border-slate-800">
                            <a
                                :href="actu.lien"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600/15 text-blue-300 border border-blue-500/30 text-xs font-bold hover:bg-blue-600/25 transition w-full justify-center"
                            >
                                <span>Consulter l'article officiel</span>
                                <span>↗</span>
                            </a>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>
</template>
