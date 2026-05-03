<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - <?= APP_NAME ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f3',
                            100: '#fde6e8',
                            200: '#fbd0d5',
                            300: '#f7aab2',
                            400: '#f17a89',
                            500: '#e74c60',
                            600: '#d02e47',
                            700: '#8b1538',
                            800: '#7a1530',
                            900: '#6a142b',
                        },
                        roman: {
                            marble: '#faf9f7',
                            gold: '#d4af37',
                            wine: '#8b1538',
                            navy: '#1e3a5f',
                            bronze: '#6b4423',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/output.css">
</head>
<body class="h-full bg-gradient-to-br from-primary-600 to-primary-800">

    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

            <!-- Logo y Título -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Restablecer Contraseña</h1>
                <p class="text-primary-100">Ingrese su nueva contraseña</p>
            </div>

            <!-- Card de Reset -->
            <div class="bg-white rounded-lg shadow-xl p-8">

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php
                        $flash = $_SESSION['flash'];
                        unset($_SESSION['flash']);
                    ?>
                    <div class="mb-6 alert alert-<?= $flash['type'] ?>">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="/reset-password" method="POST" class="space-y-6" x-data="passwordReset()">
                    <?= \App\Utils\Security::csrfField() ?>

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <!-- Nueva Contraseña -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Nueva Contraseña
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="password"
                                   name="password"
                                   class="form-input pr-10"
                                   placeholder="Ingrese su nueva contraseña"
                                   x-model="password"
                                   @input="validatePassword"
                                   required
                                   autofocus>
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Indicador de Fortaleza -->
                        <div class="mt-2">
                            <div class="flex gap-1 mb-1">
                                <div class="h-1 flex-1 rounded" :class="strength >= 1 ? 'bg-red-500' : 'bg-gray-200'"></div>
                                <div class="h-1 flex-1 rounded" :class="strength >= 2 ? 'bg-yellow-500' : 'bg-gray-200'"></div>
                                <div class="h-1 flex-1 rounded" :class="strength >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
                            </div>
                            <p class="text-xs" :class="{
                                'text-red-600': strength === 1,
                                'text-yellow-600': strength === 2,
                                'text-green-600': strength === 3,
                                'text-gray-500': strength === 0
                            }" x-text="strengthText"></p>
                        </div>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">
                            Confirmar Contraseña
                        </label>
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password_confirm"
                               name="password_confirm"
                               class="form-input"
                               placeholder="Confirme su nueva contraseña"
                               x-model="passwordConfirm"
                               required>
                        <p x-show="passwordConfirm && password !== passwordConfirm" class="form-error">
                            Las contraseñas no coinciden
                        </p>
                    </div>

                    <!-- Requisitos -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">La contraseña debe contener:</p>
                        <ul class="space-y-1 text-xs text-gray-600">
                            <li class="flex items-center" :class="requirements.length ? 'text-green-600' : ''">
                                <svg class="w-4 h-4 mr-2" :class="requirements.length ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Mínimo 8 caracteres
                            </li>
                            <li class="flex items-center" :class="requirements.uppercase ? 'text-green-600' : ''">
                                <svg class="w-4 h-4 mr-2" :class="requirements.uppercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Una letra mayúscula
                            </li>
                            <li class="flex items-center" :class="requirements.lowercase ? 'text-green-600' : ''">
                                <svg class="w-4 h-4 mr-2" :class="requirements.lowercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Una letra minúscula
                            </li>
                            <li class="flex items-center" :class="requirements.number ? 'text-green-600' : ''">
                                <svg class="w-4 h-4 mr-2" :class="requirements.number ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Un número
                            </li>
                            <li class="flex items-center" :class="requirements.special ? 'text-green-600' : ''">
                                <svg class="w-4 h-4 mr-2" :class="requirements.special ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Un carácter especial (@$!%*?&)
                            </li>
                        </ul>
                    </div>

                    <!-- Botón Submit -->
                    <button type="submit"
                            class="btn btn-primary w-full"
                            :disabled="!isValid"
                            :class="{ 'opacity-50 cursor-not-allowed': !isValid }">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Restablecer Contraseña
                    </button>
                </form>

                <!-- Link -->
                <div class="mt-6 text-center">
                    <a href="/login" class="text-sm text-primary-600 hover:text-primary-700">
                        ← Volver al inicio de sesión
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-primary-100">
                <p>&copy; <?= date('Y') ?> A Ratio. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
    function passwordReset() {
        return {
            password: '',
            passwordConfirm: '',
            showPassword: false,
            strength: 0,
            strengthText: '',
            requirements: {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            },

            get isValid() {
                return Object.values(this.requirements).every(v => v) &&
                       this.password === this.passwordConfirm &&
                       this.password.length > 0;
            },

            validatePassword() {
                const pwd = this.password;

                this.requirements.length = pwd.length >= 8;
                this.requirements.uppercase = /[A-Z]/.test(pwd);
                this.requirements.lowercase = /[a-z]/.test(pwd);
                this.requirements.number = /[0-9]/.test(pwd);
                this.requirements.special = /[@$!%*?&]/.test(pwd);

                const count = Object.values(this.requirements).filter(v => v).length;

                if (count === 5) {
                    this.strength = 3;
                    this.strengthText = 'Contraseña fuerte';
                } else if (count >= 3) {
                    this.strength = 2;
                    this.strengthText = 'Contraseña media';
                } else if (count >= 1) {
                    this.strength = 1;
                    this.strengthText = 'Contraseña débil';
                } else {
                    this.strength = 0;
                    this.strengthText = '';
                }
            }
        }
    }
    </script>

</body>
</html>
