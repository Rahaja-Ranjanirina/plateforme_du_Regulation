
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

const features = [
    {
        icon: '▥',
        title: 'Sondages',
        description: 'Exprimez votre avis sur la qualité des services.',
        color: 'blue',
    },
    {
        icon: '▤',
        title: 'Échanges',
        description: 'Communiquez directement avec les opérateurs.',
        color: 'orange',
    },
    {
        icon: '◈',
        title: 'Offres & Actualités',
        description: 'Découvrez les dernières offres et actualités.',
        color: 'green',
    },
    {
        icon: '⬡',
        title: 'Transparence',
        description: 'Une régulation au service de tous.',
        color: 'red',
    },
]
</script>

<template>
    <Head title="Accueil — Plateforme de régulation" />

    <main class="landing relative min-h-screen overflow-hidden text-white">
        <!-- Lumières floutées -->
        <div class="glow glow-blue"></div>
        <div class="glow glow-orange"></div>
        <div class="glow glow-green"></div>
        <div class="glow glow-red"></div>

        <div class="relative z-10 mx-auto max-w-[1440px] px-5 pb-8 pt-5 sm:px-8 lg:px-12">
            <!-- Navigation -->
            <header class="nav-glass flex flex-wrap items-center justify-between gap-4 rounded-[26px] px-5 py-4 sm:px-7">
                <a href="/" class="flex items-center gap-3">
                    <div class="brand-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <span class="text-lg font-bold tracking-tight sm:text-xl">
                        Plateforme de régulation
                    </span>
                </a>

                <nav class="hidden items-center gap-7 text-sm text-blue-100/80 md:flex">
                    <a href="#accueil" class="nav-link active">Accueil</a>
                    <a href="#apropos" class="nav-link">À propos</a>
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#contact" class="nav-link">Contact</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    <Link href="/connexion" class="btn-outline">
                        Se connecter
                    </Link>

                    <Link href="/inscription" class="btn-primary">
                        Créer un compte
                    </Link>
                </div>
            </header>

            <!-- Section principale -->
            <section
                id="accueil"
                class="grid items-center gap-12 py-14 md:py-20 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16"
            >
                <div class="max-w-3xl">
                    <div class="mb-6 inline-flex items-center gap-3 rounded-full border border-blue-300/30 bg-blue-400/10 px-4 py-2 text-sm text-blue-100 backdrop-blur-xl">
                        <span class="status-dot"></span>
                        Une voix pour mieux réguler
                    </div>

                    <h1 class="text-4xl font-extrabold leading-[1.12] tracking-tight sm:text-5xl lg:text-6xl">
                        Ensemble pour une
                        <span class="gradient-text">
                            meilleure qualité de service
                        </span>
                    </h1>

                    <p class="mt-7 max-w-2xl text-base leading-8 text-blue-100/75 sm:text-lg">
                        Donnez votre avis sur les télécommunications,
                        évaluez les services des opérateurs et contribuez
                        à une meilleure qualité de service pour tous.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-4">
                        <Link href="/inscription" class="btn-primary btn-large">
                            Commencer maintenant
                            <span aria-hidden="true">→</span>
                        </Link>

                        <a href="#apropos" class="btn-outline btn-large">
                            En savoir plus
                        </a>
                    </div>

                    <!-- Cartes de fonctionnalités -->
                    <div id="services" class="mt-14 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <article
                            v-for="feature in features"
                            :key="feature.title"
                            class="feature-card"
                            :class="`feature-${feature.color}`"
                        >
                            <div class="feature-icon" aria-hidden="true">
                                {{ feature.icon }}
                            </div>

                            <h2 class="mt-4 text-sm font-semibold">
                                {{ feature.title }}
                            </h2>

                            <p class="mt-2 text-xs leading-5 text-blue-100/65">
                                {{ feature.description }}
                            </p>
                        </article>
                    </div>
                </div>

                <!-- Panneau visuel en verre dépoli -->
                <div id="apropos" class="visual-card relative mx-auto w-full max-w-xl">
                    <div class="visual-background"></div>
                    <div class="visual-shine"></div>

                    <div class="relative z-10 flex min-h-[560px] flex-col p-7 sm:p-10">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100/75">
                                Connectivité · Confiance · Qualité
                            </span>

                            <h2 class="mt-5 max-w-sm text-3xl font-bold leading-tight sm:text-4xl">
                                Des services de qualité,
                                c'est possible !
                            </h2>

                            <div class="mt-5 flex gap-2">
                                <span class="h-1 w-10 rounded-full bg-blue-400"></span>
                                <span class="h-1 w-5 rounded-full bg-orange-400"></span>
                                <span class="h-1 w-5 rounded-full bg-red-400"></span>
                                <span class="h-1 w-5 rounded-full bg-green-400"></span>
                            </div>
                        </div>

                        <!-- Illustration CSS : antenne télécom -->
                        <div class="tower-scene" aria-hidden="true">
                            <div class="signal signal-left signal-left-one"></div>
                            <div class="signal signal-left signal-left-two"></div>
                            <div class="signal signal-right signal-right-one"></div>
                            <div class="signal signal-right signal-right-two"></div>

                            <div class="tower">
                                <div class="tower-top"></div>
                                <div class="tower-cross cross-one"></div>
                                <div class="tower-cross cross-two"></div>
                                <div class="tower-leg leg-left"></div>
                                <div class="tower-leg leg-right"></div>
                                <div class="tower-brace brace-one"></div>
                                <div class="tower-brace brace-two"></div>
                                <div class="tower-brace brace-three"></div>
                                <div class="tower-base"></div>
                            </div>

                            <div class="tower-light"></div>
                        </div>

                        <!-- Statistiques indicatives -->
                        <div class="stats-panel mt-auto grid grid-cols-3 gap-3 rounded-2xl p-4 sm:p-5">
                            <div class="text-center">
                                <div class="stat-icon stat-blue">◎</div>
                                <div class="mt-2 text-sm font-bold sm:text-base">
                                    4 critères
                                </div>
                                <div class="mt-1 text-[11px] text-blue-100/65 sm:text-xs">
                                    À évaluer
                                </div>
                            </div>

                            <div class="text-center">
                                <div class="stat-icon stat-orange">↗</div>
                                <div class="mt-2 text-sm font-bold sm:text-base">
                                    Plusieurs
                                </div>
                                <div class="mt-1 text-[11px] text-blue-100/65 sm:text-xs">
                                    Opérateurs
                                </div>
                            </div>

                            <div class="text-center">
                                <div class="stat-icon stat-green">✓</div>
                                <div class="mt-2 text-sm font-bold sm:text-base">
                                    Votre avis
                                </div>
                                <div class="mt-1 text-[11px] text-blue-100/65 sm:text-xs">
                                    Compte
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bandeau inférieur -->
            <section class="bottom-banner flex flex-col gap-4 rounded-2xl p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">
                        Notre engagement
                    </p>
                    <h2 class="mt-2 text-lg font-bold sm:text-xl">
                        Une meilleure qualité de télécommunications pour tous.
                    </h2>
                </div>

                <Link href="/inscription" class="btn-primary shrink-0 self-start sm:self-auto">
                    Rejoindre la plateforme <span aria-hidden="true">→</span>
                </Link>
            </section>

            <!-- Pied de page -->
            <footer
                id="contact"
                class="mt-8 flex flex-col justify-between gap-3 border-t border-white/10 pt-6 text-sm text-blue-100/50 sm:flex-row"
            >
                <p>
                    © {{ new Date().getFullYear() }} — Plateforme de régulation
                </p>

                <div class="flex flex-wrap gap-4">
                    <a href="#apropos" class="footer-link">À propos</a>
                    <a href="#services" class="footer-link">Services</a>
                    <a href="mailto:contact@regulation.test" class="footer-link">Contact</a>
                </div>
            </footer>
        </div>
    </main>
</template>

<style scoped>
/* =========================
   FOND PRINCIPAL
========================= */

.landing {
    isolation: isolate;
    background:
        radial-gradient(ellipse at 12% 0%, rgba(37, 99, 235, .22), transparent 36%),
        linear-gradient(135deg, #07152f 0%, #0d1934 48%, #17152b 100%);
}

.glow {
    position: absolute;
    z-index: -1;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    filter: blur(115px);
    pointer-events: none;
    opacity: .25;
}

.glow-blue {
    top: -5%;
    left: -12%;
    background: #2563eb;
}

.glow-orange {
    top: 25%;
    right: -12%;
    background: #f97316;
}

.glow-green {
    bottom: -15%;
    left: 30%;
    background: #16a34a;
    opacity: .16;
}

.glow-red {
    top: 55%;
    left: -15%;
    background: #ef4444;
    opacity: .12;
}

/* =========================
   NAVIGATION
========================= */

.nav-glass {
    border: 1px solid rgba(255, 255, 255, .11);
    background: rgba(255, 255, 255, .055);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    box-shadow: 0 12px 50px rgba(0, 0, 0, .13);
}

.brand-icon {
    display: flex;
    height: 34px;
    align-items: end;
    gap: 4px;
}

.brand-icon span {
    width: 9px;
    border-radius: 4px;
    background: linear-gradient(180deg, #93c5fd, #2563eb);
    box-shadow: 0 0 15px rgba(59, 130, 246, .35);
}

.brand-icon span:nth-child(1) {
    height: 17px;
}

.brand-icon span:nth-child(2) {
    height: 26px;
}

.brand-icon span:nth-child(3) {
    height: 33px;
}

.nav-link,
.footer-link {
    transition: color .2s ease;
}

.nav-link:hover,
.nav-link.active {
    color: #60a5fa;
}

.footer-link:hover {
    color: #fb923c;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 12px rgba(74, 222, 128, .75);
}

/* =========================
   BOUTONS
========================= */

.btn-primary,
.btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border-radius: 999px;
    padding: 12px 18px;
    font-size: 13px;
    font-weight: 600;
    transition:
        transform .2s ease,
        background .2s ease,
        box-shadow .2s ease;
}

.btn-primary {
    border: 1px solid rgba(255, 255, 255, .1);
    color: white;
    background: linear-gradient(110deg, #1d4ed8, #3b82f6);
    box-shadow: 0 8px 28px rgba(37, 99, 235, .23);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 34px rgba(37, 99, 235, .4);
}

.btn-outline {
    border: 1px solid rgba(191, 219, 254, .23);
    color: #e5edff;
    background: rgba(255, 255, 255, .035);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.btn-outline:hover {
    border-color: rgba(251, 146, 60, .5);
    background: rgba(249, 115, 22, .09);
}

.btn-large {
    padding: 16px 25px;
    font-size: 14px;
}

/* =========================
   TITRE MULTICOLORE
========================= */

.gradient-text {
    display: block;
    padding-top: 5px;
    background: linear-gradient(
        90deg,
        #60a5fa 0%,
        #fb923c 38%,
        #f87171 68%,
        #4ade80 100%
    );
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

/* =========================
   CARTES DES SERVICES
========================= */

.feature-card {
    min-height: 178px;
    padding: 17px 14px;
    border: 1px solid rgba(191, 219, 254, .12);
    border-radius: 18px;
    background: rgba(255, 255, 255, .045);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    transition:
        transform .25s ease,
        background .25s ease,
        border-color .25s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    display: grid;
    width: 43px;
    height: 43px;
    place-items: center;
    border-radius: 14px;
    font-size: 23px;
    color: white;
    background: var(--feature-color);
    box-shadow: 0 7px 22px var(--feature-shadow);
}

.feature-blue {
    --feature-color: #2563eb;
    --feature-shadow: rgba(37, 99, 235, .25);
}

.feature-orange {
    --feature-color: #ea580c;
    --feature-shadow: rgba(249, 115, 22, .25);
}

.feature-green {
    --feature-color: #16a34a;
    --feature-shadow: rgba(22, 163, 74, .25);
}

.feature-red {
    --feature-color: #dc2626;
    --feature-shadow: rgba(239, 68, 68, .25);
}

.feature-blue:hover {
    border-color: rgba(96, 165, 250, .4);
    background: rgba(37, 99, 235, .09);
}

.feature-orange:hover {
    border-color: rgba(251, 146, 60, .4);
    background: rgba(249, 115, 22, .08);
}

.feature-green:hover {
    border-color: rgba(74, 222, 128, .4);
    background: rgba(22, 163, 74, .08);
}

.feature-red:hover {
    border-color: rgba(248, 113, 113, .4);
    background: rgba(239, 68, 68, .08);
}

/* =========================
   PANNEAU VISUEL
========================= */

.visual-card {
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .17);
    border-radius: 28px;
    background: rgba(255, 255, 255, .055);
    box-shadow: 0 30px 100px rgba(0, 0, 0, .28);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
}

.visual-background {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse at 80% 20%, rgba(249, 115, 22, .22), transparent 32%),
        radial-gradient(ellipse at 20% 70%, rgba(37, 99, 235, .3), transparent 40%),
        linear-gradient(150deg, #233f78, #111e3b 65%, #17152c);
}

.visual-shine {
    position: absolute;
    top: -30%;
    right: -20%;
    width: 90%;
    height: 55%;
    border-radius: 50%;
    background: rgba(96, 165, 250, .12);
    filter: blur(55px);
}

/* =========================
   ANTENNE TÉLÉCOM EN CSS
========================= */

.tower-scene {
    position: relative;
    height: 290px;
    margin-top: 15px;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, .09);
    background:
        radial-gradient(ellipse at 50% 95%, rgba(251, 146, 60, .19), transparent 50%);
}

.tower {
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 100px;
    height: 230px;
    transform: translateX(-50%);
}

.tower-top {
    position: absolute;
    top: 0;
    left: 47px;
    width: 6px;
    height: 35px;
    border-radius: 5px;
    background: #bfdbfe;
    box-shadow: 0 0 15px rgba(147, 197, 253, .65);
}

.tower-cross {
    position: absolute;
    left: 16px;
    width: 68px;
    height: 5px;
    border-radius: 5px;
    background: linear-gradient(90deg, #60a5fa, #e2e8f0, #fb923c);
}

.cross-one {
    top: 55px;
}

.cross-two {
    top: 112px;
}

.tower-leg {
    position: absolute;
    top: 25px;
    bottom: 0;
    width: 5px;
    border-radius: 5px;
    background: linear-gradient(180deg, #dbeafe, #64748b);
}

.leg-left {
    left: 23px;
    transform: rotate(8deg);
    transform-origin: top;
}

.leg-right {
    right: 23px;
    transform: rotate(-8deg);
    transform-origin: top;
}

.tower-brace {
    position: absolute;
    left: 25px;
    width: 51px;
    height: 3px;
    background: rgba(191, 219, 254, .75);
    transform: rotate(-25deg);
}

.brace-one {
    top: 75px;
}

.brace-two {
    top: 120px;
    transform: rotate(25deg);
}

.brace-three {
    top: 165px;
    transform: rotate(-25deg);
}

.tower-base {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100px;
    height: 7px;
    border-radius: 50%;
    background: #93c5fd;
    box-shadow: 0 0 25px rgba(96, 165, 250, .5);
}

.tower-light {
    position: absolute;
    top: 37px;
    left: calc(50% + 1px);
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 18px rgba(239, 68, 68, .9);
}

.signal {
    position: absolute;
    top: 58px;
    width: 50px;
    height: 90px;
    border: 2px solid rgba(147, 197, 253, .65);
    border-top-color: transparent;
    border-bottom-color: transparent;
    border-radius: 50%;
}

.signal-left {
    left: calc(50% - 78px);
}

.signal-right {
    right: calc(50% - 78px);
}

.signal-left-one {
    width: 35px;
    transform: rotate(12deg);
}

.signal-left-two {
    left: calc(50% - 104px);
    width: 57px;
    opacity: .5;
    transform: rotate(12deg);
}

.signal-right-one {
    width: 35px;
    transform: rotate(-12deg);
}

.signal-right-two {
    right: calc(50% - 104px);
    width: 57px;
    opacity: .5;
    transform: rotate(-12deg);
}

/* =========================
   STATISTIQUES
========================= */

.stats-panel {
    border: 1px solid rgba(255, 255, 255, .14);
    background: rgba(8, 18, 42, .48);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
}

.stat-icon {
    display: inline-grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 11px;
    font-size: 22px;
    font-weight: 700;
}

.stat-blue {
    color: #93c5fd;
    background: rgba(37, 99, 235, .2);
}

.stat-orange {
    color: #fdba74;
    background: rgba(249, 115, 22, .18);
}

.stat-green {
    color: #86efac;
    background: rgba(22, 163, 74, .18);
}

/* =========================
   BANDEAU ET RESPONSIVE
========================= */

.bottom-banner {
    border: 1px solid rgba(255, 255, 255, .1);
    background: rgba(255, 255, 255, .045);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

@media (max-width: 640px) {
    .visual-card .min-h-\[560px\] {
        min-height: 510px;
    }

    .tower-scene {
        height: 245px;
    }

    .tower {
        transform: translateX(-50%) scale(.85);
        transform-origin: bottom center;
    }

    .feature-card {
        min-height: 160px;
        padding: 14px 12px;
    }

    .btn-primary,
    .btn-outline {
        padding: 11px 14px;
        font-size: 12px;
    }

    .btn-large {
        padding: 14px 18px;
        font-size: 13px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>
