<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'

interface UserItem {
    id: number
    name: string
    email: string
    role: string
    created_at: string
    operateur?: {
        id: number
        nom: string
    }
}

interface OperateurItem {
    id: number
    nom: string
    user_id: number | null
}

const props = defineProps<{
    stats: {
        total_users: number
        total_artec: number
        total_operateurs: number
        total_visiteurs: number
        total_sondages: number
        total_reponses: number
        total_offres: number
        total_actualites: number
    }
    artecUsers: UserItem[]
    operateurUsers: UserItem[]
    visiteurs: UserItem[]
    operateurs: OperateurItem[]
}>()

const page = usePage()
const activeTab = ref<'managers' | 'visiteurs'>('managers')
const showCreateModal = ref(false)

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'artec',
    operateur_id: props.operateurs[0]?.id ?? null,
})

function submitCreate() {
    createForm.post('/admin/users', {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

function deleteUser(user: UserItem) {
    if (confirm(`Êtes-vous certain de vouloir supprimer le compte "${user.name}" (${user.email}) ?`)) {
        router.delete(`/admin/users/${user.id}`)
    }
}

function logout() {
    router.post('/deconnexion')
}
</script>

<template>
    <Head title="Administration — Plateforme de Régulation" />

    <div class="admin-app min-h-screen bg-[#0b1120] text-slate-100 flex flex-col selection:bg-indigo-500/30">
        <!-- Barre de navigation supérieure -->
        <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-600/30 font-black text-white text-lg">
                        🛡️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-bold text-white tracking-tight">Espace Administrateur</h1>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                Super Admin
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400">Plateforme de Régulation des Télécommunications</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/50 border border-slate-700/60 text-xs text-slate-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ page.props.auth?.user?.email }}</span>
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

        <!-- Messages Flash -->
        <div v-if="page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                <span>✓</span>
                <span>{{ page.props.flash.success }}</span>
            </div>
        </div>

        <div v-if="page.props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs flex items-center gap-2">
                <span>⚠</span>
                <span>{{ page.props.flash.error }}</span>
            </div>
        </div>

        <!-- Corps principal -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
            <!-- Bannière d'introduction -->
            <section class="rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-indigo-950/40 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xl">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400">Contrôle & Accès</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">
                        Gestion centralisée des comptes
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-2 max-w-2xl leading-relaxed">
                        L'administrateur gère les accès institutionnels de l'<strong>ARTEC</strong> et des <strong>3 opérateurs (Yas, Airtel, Orange)</strong>. Les visiteurs s'enregistrent quant à eux de manière autonome avec leur e-mail.
                    </p>
                </div>

                <button
                    type="button"
                    class="shrink-0 flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold text-xs shadow-xl shadow-indigo-600/30 hover:brightness-110 transition active:scale-95"
                    @click="showCreateModal = true"
                >
                    <span class="text-base leading-none">＋</span>
                    <span>Ajouter un compte ARTEC / Opérateur</span>
                </button>
            </section>

            <!-- Grille de statistiques -->
            <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-md">
                    <span class="text-xs text-slate-400 font-medium">Total Comptes</span>
                    <div class="text-2xl sm:text-3xl font-black text-white mt-2">{{ props.stats.total_users }}</div>
                    <span class="text-[10px] text-indigo-400 mt-1 block">Tous rôles confondus</span>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-md">
                    <span class="text-xs text-slate-400 font-medium">Opérateurs Actifs</span>
                    <div class="text-2xl sm:text-3xl font-black text-amber-400 mt-2">{{ props.stats.total_operateurs }}</div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Yas, Airtel & Orange</span>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-md">
                    <span class="text-xs text-slate-400 font-medium">Visiteurs Inscrits</span>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-400 mt-2">{{ props.stats.total_visiteurs }}</div>
                    <span class="text-[10px] text-emerald-500/80 mt-1 block">Comptes sans mot de passe</span>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-md">
                    <span class="text-xs text-slate-400 font-medium">Avis Visiteurs</span>
                    <div class="text-2xl sm:text-3xl font-black text-cyan-400 mt-2">{{ props.stats.total_reponses }}</div>
                    <span class="text-[10px] text-cyan-500/80 mt-1 block">Notes & Satisfactions</span>
                </div>
            </section>

            <!-- Onglets de gestion -->
            <section class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition"
                            :class="activeTab === 'managers' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-white bg-slate-800/40'"
                            @click="activeTab = 'managers'"
                        >
                            Comptes ARTEC & Opérateurs ({{ props.artecUsers.length + props.operateurUsers.length }})
                        </button>
                        <button
                            type="button"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition"
                            :class="activeTab === 'visiteurs' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-white bg-slate-800/40'"
                            @click="activeTab = 'visiteurs'"
                        >
                            Comptes Visiteurs ({{ props.visiteurs.length }})
                        </button>
                    </div>

                    <div class="text-[11px] text-slate-400">
                        <span v-if="activeTab === 'managers'">Vous pouvez ajouter ou révoquer ces comptes.</span>
                        <span v-else>Les visiteurs créent leur compte en toute autonomie.</span>
                    </div>
                </div>

                <!-- Tableau ARTEC & Opérateurs -->
                <div v-if="activeTab === 'managers'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 text-[11px] uppercase tracking-wider font-semibold">
                                <th class="pb-3 px-3">Nom</th>
                                <th class="pb-3 px-3">E-mail</th>
                                <th class="pb-3 px-3">Rôle</th>
                                <th class="pb-3 px-3">Opérateur attribué</th>
                                <th class="pb-3 px-3">Date création</th>
                                <th class="pb-3 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <!-- Comptes ARTEC -->
                            <tr v-for="u in props.artecUsers" :key="u.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-3 font-semibold text-white flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                    <span>{{ u.name }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-300 font-mono">{{ u.email }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                                        ARTEC
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-400">Régulateur National</td>
                                <td class="py-3 px-3 text-slate-500">{{ new Date(u.created_at).toLocaleDateString() }}</td>
                                <td class="py-3 px-3 text-right">
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg bg-red-500/10 text-red-300 border border-red-500/20 hover:bg-red-500/20 text-[11px] font-medium transition"
                                        @click="deleteUser(u)"
                                    >
                                        Supprimer
                                    </button>
                                </td>
                            </tr>

                            <!-- Comptes Opérateurs -->
                            <tr v-for="u in props.operateurUsers" :key="u.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-3 font-semibold text-white flex items-center gap-2">
                                    <span
                                        class="h-2 w-2 rounded-full"
                                        :class="u.name.toLowerCase().includes('yas') ? 'bg-yellow-400' : u.name.toLowerCase().includes('airtel') ? 'bg-red-500' : 'bg-orange-500'"
                                    ></span>
                                    <span>{{ u.name }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-300 font-mono">{{ u.email }}</td>
                                <td class="py-3 px-3">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                        :class="u.name.toLowerCase().includes('yas') ? 'bg-yellow-500/10 text-yellow-300 border-yellow-500/30' : u.name.toLowerCase().includes('airtel') ? 'bg-red-500/10 text-red-300 border-red-500/30' : 'bg-orange-500/10 text-orange-300 border-orange-500/30'"
                                    >
                                        Opérateur
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-200">
                                    {{ u.operateur?.nom ?? u.name }}
                                </td>
                                <td class="py-3 px-3 text-slate-500">{{ new Date(u.created_at).toLocaleDateString() }}</td>
                                <td class="py-3 px-3 text-right">
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg bg-red-500/10 text-red-300 border border-red-500/20 hover:bg-red-500/20 text-[11px] font-medium transition"
                                        @click="deleteUser(u)"
                                    >
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tableau Visiteurs -->
                <div v-else class="overflow-x-auto">
                    <div class="mb-4 p-3 rounded-xl bg-slate-800/40 border border-slate-700/60 text-xs text-slate-300 flex items-center gap-2">
                        <span class="text-indigo-400">ℹ</span>
                        <span>Seuls les visiteurs créent leur compte (via inscription par e-mail). L'admin ne crée pas de compte visiteur.</span>
                    </div>

                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 text-[11px] uppercase tracking-wider font-semibold">
                                <th class="pb-3 px-3">Nom Visiteur (sans @gmail.com)</th>
                                <th class="pb-3 px-3">Adresse e-mail</th>
                                <th class="pb-3 px-3">Rôle</th>
                                <th class="pb-3 px-3">Mode d'authentification</th>
                                <th class="pb-3 px-3 text-right">Inscrit le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <tr v-for="v in props.visiteurs" :key="v.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-3 font-bold text-emerald-300">
                                    {{ v.name }}
                                </td>
                                <td class="py-3 px-3 text-slate-300 font-mono">{{ v.email }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                                        Visiteur
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-400">E-mail seul (sans mot de passe)</td>
                                <td class="py-3 px-3 text-slate-500 text-right">{{ new Date(v.created_at).toLocaleDateString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <!-- Modal de création de compte -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            <div class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8 shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-white">Nouveau compte habilité</h3>
                        <p class="text-xs text-slate-400">Ajouter un compte ARTEC ou Opérateur</p>
                    </div>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-white text-lg font-bold p-1"
                        @click="showCreateModal = false"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Rôle du compte</label>
                        <select
                            v-model="createForm.role"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-indigo-500"
                        >
                            <option value="artec">ARTEC (Régulateur)</option>
                            <option value="operateur">Opérateur Télécom</option>
                        </select>
                    </div>

                    <div v-if="createForm.role === 'operateur'">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Opérateur concerné</label>
                        <select
                            v-model="createForm.operateur_id"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-indigo-500"
                        >
                            <option v-for="op in props.operateurs" :key="op.id" :value="op.id">
                                {{ op.nom }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nom du compte / Entité</label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            required
                            placeholder="ex: Yas Officiel ou Inspecteur ARTEC"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-indigo-500"
                        />
                        <p v-if="createForm.errors.name" class="text-[11px] text-red-400 mt-1">{{ createForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Adresse e-mail</label>
                        <input
                            v-model="createForm.email"
                            type="email"
                            required
                            placeholder="ex: contact@yas.mg ou inspecteur@artec.mg"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-indigo-500"
                        />
                        <p v-if="createForm.errors.email" class="text-[11px] text-red-400 mt-1">{{ createForm.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Mot de passe provisoire</label>
                        <input
                            v-model="createForm.password"
                            type="password"
                            required
                            placeholder="Minimum 6 caractères"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-xs text-white outline-none focus:border-indigo-500"
                        />
                        <p v-if="createForm.errors.password" class="text-[11px] text-red-400 mt-1">{{ createForm.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800 transition"
                            @click="showCreateModal = false"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition disabled:opacity-50"
                        >
                            {{ createForm.processing ? 'Création en cours...' : 'Créer le compte' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
