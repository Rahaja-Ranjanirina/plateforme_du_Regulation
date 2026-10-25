
<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
})

function submit() {
    form.post('/inscription')
}
</script>

<template>
    <Head title="Inscription — Plateforme de régulation" />

    <main class="register-page relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 text-white sm:px-6">
        <!-- Lumières floutées -->
        <div class="glow glow-blue"></div>
        <div class="glow glow-orange"></div>
        <div class="glow glow-green"></div>
        <div class="glow glow-red"></div>

        <div class="relative z-10 w-full max-w-6xl">
            <!-- Retour à l'accueil -->
            <div class="mb-6 flex items-center justify-between">
                <a href="/" class="brand flex items-center gap-3">
                    <div class="brand-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="font-bold tracking-tight">
                        Plateforme de régulation
                    </span>
                </a>

                <a href="/" class="back-link">
                    <span aria-hidden="true">←</span>
                    <span class="hidden sm:inline">Retour à l'accueil</span>
                    <span class="sm:hidden">Accueil</span>
                </a>
            </div>

            <!-- Carte principale -->
            <section class="register-card grid overflow-hidden lg:grid-cols-2">
                <!-- Partie présentation -->
                <div class="intro-panel relative flex flex-col justify-between p-7 sm:p-10 lg:p-12">
                    <div class="relative z-10">
                        <div class="eyebrow">
                            <span class="status-dot"></span>
                            Rejoignez la communauté
                        </div>

                        <h1 class="mt-7 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                            Votre avis
                            <span class="gradient-text">fait la différence.</span>
                        </h1>

                        <p class="mt-6 max-w-md leading-7 text-blue-100/75">
                            Participez à l'amélioration des télécommunications.
                            Évaluez les services et faites entendre votre voix
                            grâce à une plateforme accessible à tous.
                        </p>
                    </div>

                    <!-- Les quatre couleurs -->
                    <div class="color-indicators mt-12 flex items-center gap-3">
                        <span class="color-dot dot-blue"></span>
                        <span class="color-dot dot-orange"></span>
                        <span class="color-dot dot-red"></span>
                        <span class="color-dot dot-green"></span>
                        <span class="ml-2 text-xs text-blue-100/55">
                            Écoute · Qualité · Transparence
                        </span>
                    </div>

                    <div class="benefits mt-8 space-y-5">
                        <div class="benefit">
                            <span class="benefit-icon blue-icon">✓</span>
                            <div>
                                <h2 class="font-semibold">Inscription simple</h2>
                                <p class="mt-1 text-sm text-blue-100/60">
                                    Une adresse e-mail suffit.
                                </p>
                            </div>
                        </div>

                        <div class="benefit">
                            <span class="benefit-icon orange-icon">◈</span>
                            <div>
                                <h2 class="font-semibold">Donnez votre avis</h2>
                                <p class="mt-1 text-sm text-blue-100/60">
                                    Évaluez les services des opérateurs.
                                </p>
                            </div>
                        </div>

                        <div class="benefit">
                            <span class="benefit-icon green-icon">◎</span>
                            <div>
                                <h2 class="font-semibold">Contribuez au changement</h2>
                                <p class="mt-1 text-sm text-blue-100/60">
                                    Aidez à identifier les améliorations nécessaires.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-10 text-xs text-blue-100/40">
                        Une meilleure qualité de télécommunications pour tous.
                    </p>
                </div>

                <!-- Formulaire -->
                <div class="form-panel flex items-center justify-center p-7 sm:p-10 lg:p-12">
                    <div class="w-full max-w-md">
                        <div class="mb-8">
                            <div class="form-symbol">✦</div>

                            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.22em] text-blue-300">
                                Créer votre espace
                            </p>

                            <h2 class="mt-3 text-3xl font-bold tracking-tight">
                                Inscription
                            </h2>

                            <p class="mt-3 text-sm leading-6 text-blue-100/60">
                                Entrez votre adresse e-mail pour créer
                                votre compte visiteur.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="email" class="field-label">
                                    Adresse e-mail
                                </label>

                                <div class="input-wrapper" :class="{ 'input-error': form.errors.email }">
                                    <span class="input-icon" aria-hidden="true">✉</span>

                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        name="email"
                                        placeholder="exemple@email.com"
                                        autocomplete="email"
                                        required
                                        autofocus
                                        class="form-input"
                                    />
                                </div>

                                <p v-if="form.errors.email" class="error-message">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="info-box">
                                <span class="info-icon">i</span>
                                <p>
                                    Aucun mot de passe n'est nécessaire pour
                                    créer un compte visiteur. Utilisez une
                                    adresse e-mail valide.
                                </p>
                            </div>

                            <button
                                type="submit"
                                class="submit-button"
                                :disabled="form.processing"
                            >
                                <span>
                                    {{ form.processing ? 'Création en cours...' : 'Créer mon compte' }}
                                </span>
                                <span v-if="!form.processing" aria-hidden="true">→</span>
                                <span v-else class="spinner"></span>
                            </button>
                        </form>

                        <div class="separator">
                            <span></span>
                            <p>Vous avez déjà un compte ?</p>
                            <span></span>
                        </div>

                        <Link href="/connexion" class="login-button">
                            Se connecter
                        </Link>

                        <p class="mt-8 text-center text-xs leading-5 text-blue-100/40">
                            En créant votre compte, vous accédez à l'espace
                            réservé aux visiteurs de la plateforme.
                        </p>
                    </div>
                </div>
            </section>

            <footer class="mt-6 flex flex-col items-center justify-between gap-3 text-xs text-blue-100/40 sm:flex-row">
                <p>© {{ new Date().getFullYear() }} Plateforme de régulation</p>
                <a href="/" class="footer-link">Retour à l'accueil</a>
            </footer>
        </div>
    </main>
</template>

<style scoped>
.register-page {
    isolation: isolate;
    background:
        radial-gradient(ellipse at 15% 0%, rgba(37, 99, 235, .23), transparent 38%),
        linear-gradient(135deg, #07152f, #101a34 55%, #19152a);
}

.glow {
    position: absolute;
    z-index: -1;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    filter: blur(115px);
    opacity: .22;
    pointer-events: none;
}

.glow-blue {
    top: -10%;
    left: -10%;
    background: #2563eb;
}

.glow-orange {
    top: 20%;
    right: -10%;
    background: #f97316;
}

.glow-green {
    bottom: -15%;
    right: 25%;
    background: #16a34a;
    opacity: .13;
}

.glow-red {
    bottom: 10%;
    left: -15%;
    background: #ef4444;
    opacity: .12;
}

/* Identité visuelle */
.brand {
    color: #f8fafc;
    text-decoration: none;
}

.brand-icon {
    display: flex;
    height: 30px;
    align-items: end;
    gap: 4px;
}

.brand-icon span {
    width: 8px;
    border-radius: 4px;
    background: linear-gradient(180deg, #93c5fd, #2563eb);
}

.brand-icon span:nth-child(1) {
    height: 15px;
}

.brand-icon span:nth-child(2) {
    height: 23px;
}

.brand-icon span:nth-child(3) {
    height: 30px;
}

.back-link,
.footer-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: rgba(219, 234, 254, .68);
    font-size: 13px;
    transition: color .2s ease;
}

.back-link:hover,
.footer-link:hover {
    color: #fb923c;
}

/* Carte glassmorphism */
.register-card {
    border: 1px solid rgba(255, 255, 255, .13);
    border-radius: 30px;
    background: rgba(255, 255, 255, .045);
    box-shadow: 0 30px 100px rgba(0, 0, 0, .3);
    backdrop-filter: blur(26px);
    -webkit-backdrop-filter: blur(26px);
}

.intro-panel {
    overflow: hidden;
    background:
        radial-gradient(ellipse at 0% 0%, rgba(37, 99, 235, .2), transparent 50%),
        rgba(255, 255, 255, .025);
}

.intro-panel::after {
    position: absolute;
    right: -120px;
    bottom: -140px;
    width: 320px;
    height: 320px;
    border: 1px solid rgba(96, 165, 250, .12);
    border-radius: 50%;
    box-shadow:
        0 0 0 35px rgba(96, 165, 250, .025),
        0 0 0 75px rgba(249, 115, 22, .025);
    content: '';
    pointer-events: none;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border: 1px solid rgba(147, 197, 253, .2);
    border-radius: 999px;
    background: rgba(59, 130, 246, .08);
    color: #dbeafe;
    font-size: 12px;
    backdrop-filter: blur(12px);
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 12px rgba(74, 222, 128, .6);
}

.gradient-text {
    display: block;
    margin-top: 5px;
    background: linear-gradient(90deg, #60a5fa, #fb923c 45%, #4ade80);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.color-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
}

.dot-blue {
    background: #3b82f6;
    box-shadow: 0 0 12px rgba(59, 130, 246, .5);
}

.dot-orange {
    background: #f97316;
    box-shadow: 0 0 12px rgba(249, 115, 22, .4);
}

.dot-red {
    background: #ef4444;
    box-shadow: 0 0 12px rgba(239, 68, 68, .4);
}

.dot-green {
    background: #22c55e;
    box-shadow: 0 0 12px rgba(34, 197, 94, .4);
}

/* Avantages */
.benefit {
    display: flex;
    align-items: center;
    gap: 14px;
}

.benefit-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .09);
    border-radius: 14px;
    font-size: 19px;
}

.blue-icon {
    color: #93c5fd;
    background: rgba(37, 99, 235, .15);
}

.orange-icon {
    color: #fdba74;
    background: rgba(249, 115, 22, .13);
}

.green-icon {
    color: #86efac;
    background: rgba(22, 163, 74, .13);
}

/* Formulaire */
.form-panel {
    background: rgba(4, 12, 29, .24);
}

.form-symbol {
    display: grid;
    width: 52px;
    height: 52px;
    place-items: center;
    border: 1px solid rgba(147, 197, 253, .2);
    border-radius: 17px;
    background: linear-gradient(145deg, rgba(37, 99, 235, .22), rgba(249, 115, 22, .1));
    color: #bfdbfe;
    font-size: 25px;
    box-shadow: 0 8px 30px rgba(37, 99, 235, .1);
}

.field-label {
    display: block;
    margin-bottom: 10px;
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 600;
}

.input-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 15px;
    border: 1px solid rgba(191, 219, 254, .17);
    border-radius: 14px;
    background: rgba(255, 255, 255, .045);
    transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
}

.input-wrapper:focus-within {
    border-color: rgba(96, 165, 250, .75);
    background: rgba(37, 99, 235, .07);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, .09);
}

.input-wrapper.input-error {
    border-color: rgba(248, 113, 113, .75);
}

.input-icon {
    color: #93c5fd;
    font-size: 18px;
}

.form-input {
    width: 100%;
    min-width: 0;
    padding: 16px 0;
    outline: none;
    border: 0;
    background: transparent;
    color: white;
    font-size: 14px;
}

.form-input::placeholder {
    color: rgba(191, 219, 254, .35);
}

.form-input:focus {
    outline: none;
    box-shadow: none;
}

.error-message {
    margin-top: 8px;
    color: #fca5a5;
    font-size: 12px;
}

.info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border: 1px solid rgba(96, 165, 250, .18);
    border-radius: 14px;
    background: rgba(37, 99, 235, .07);
    color: rgba(219, 234, 254, .72);
    font-size: 12px;
    line-height: 1.7;
}

.info-icon {
    display: grid;
    width: 19px;
    height: 19px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 50%;
    background: rgba(96, 165, 250, .2);
    color: #93c5fd;
    font-size: 12px;
    font-weight: 700;
}

.submit-button {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px 20px;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 14px;
    background: linear-gradient(105deg, #1d4ed8, #3b82f6);
    color: white;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 10px 28px rgba(37, 99, 235, .22);
    transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
}

.submit-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(37, 99, 235, .35);
}

.submit-button:disabled {
    cursor: wait;
    opacity: .7;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, .4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.separator {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 25px 0 18px;
    color: rgba(191, 219, 254, .48);
    font-size: 11px;
    white-space: nowrap;
}

.separator span {
    height: 1px;
    flex: 1;
    background: rgba(255, 255, 255, .1);
}

.login-button {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    padding: 14px;
    border: 1px solid rgba(191, 219, 254, .2);
    border-radius: 14px;
    color: #dbeafe;
    font-size: 13px;
    font-weight: 600;
    transition: border-color .2s ease, background .2s ease;
}

.login-button:hover {
    border-color: rgba(249, 115, 22, .5);
    background: rgba(249, 115, 22, .06);
}

/* Responsive */
@media (max-width: 1023px) {
    .register-card {
        max-width: 700px;
        margin-inline: auto;
    }

    .intro-panel {
        padding-bottom: 32px;
    }

    .benefits {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .benefit {
        align-items: flex-start;
    }
}

@media (max-width: 560px) {
    .register-card {
        border-radius: 22px;
    }

    .intro-panel,
    .form-panel {
        padding: 25px 20px;
    }

    .benefits {
        grid-template-columns: 1fr;
        gap: 17px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>
