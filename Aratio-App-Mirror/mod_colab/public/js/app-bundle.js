/**
 * JavaScript Principal con Imports de Dependencias
 * Este archivo será bundleado por esbuild
 */

// Import Alpine.js y plugins
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import mask from '@alpinejs/mask';

// Import otras librerías
import ApexCharts from 'apexcharts';
import Toastify from 'toastify-js';
import Swal from 'sweetalert2';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import Choices from 'choices.js';

// Configurar Alpine.js
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(mask);

// Exponer en el objeto window para acceso global
window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.Toastify = Toastify;
window.Swal = Swal;
window.flatpickr = flatpickr;
window.Choices = Choices;

// Configurar flatpickr español por defecto
flatpickr.localize(Spanish);

// Iniciar Alpine.js
Alpine.start();

// Configuración de Alpine.js Stores
document.addEventListener('alpine:init', () => {

    // Store global para el tema
    Alpine.store('theme', {
        dark: localStorage.getItem('darkMode') === 'true',

        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('darkMode', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        },

        init() {
            document.documentElement.classList.toggle('dark', this.dark);
        }
    });

    // Store global para notificaciones
    Alpine.store('notifications', {
        items: [],

        add(message, type = 'info') {
            const id = Date.now();
            this.items.push({ id, message, type });

            // Auto-remove después de 5 segundos
            setTimeout(() => {
                this.remove(id);
            }, 5000);
        },

        remove(id) {
            this.items = this.items.filter(item => item.id !== id);
        }
    });
});

// Inicializar tema al cargar
if (localStorage.getItem('darkMode') === 'true') {
    document.documentElement.classList.add('dark');
}

// Utilidades globales
window.App = {

    /**
     * Mostrar confirmación antes de una acción
     */
    confirm(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Está seguro?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        } else {
            if (confirm(message) && callback) {
                callback();
            }
        }
    },

    /**
     * Mostrar notificación toast
     */
    toast(message, type = 'success') {
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: message,
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: type === 'success' ? '#10b981' :
                                 type === 'error' ? '#ef4444' :
                                 type === 'warning' ? '#f59e0b' : '#3b82f6',
            }).showToast();
        } else {
            alert(message);
        }
    },

    /**
     * Realizar petición AJAX
     */
    async fetch(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error en la petición');
            }

            return data;
        } catch (error) {
            console.error('Error:', error);
            this.toast(error.message, 'error');
            throw error;
        }
    },

    /**
     * Copiar texto al portapapeles
     */
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.toast('Copiado al portapapeles', 'success');
            });
        } else {
            // Fallback para navegadores antiguos
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            this.toast('Copiado al portapapeles', 'success');
        }
    },

    /**
     * Formatear número
     */
    formatNumber(num) {
        return new Intl.NumberFormat('es-CO').format(num);
    },

    /**
     * Formatear fecha
     */
    formatDate(date, format = 'short') {
        const d = new Date(date);
        const options = format === 'short'
            ? { year: 'numeric', month: '2-digit', day: '2-digit' }
            : { year: 'numeric', month: 'long', day: 'numeric' };
        return d.toLocaleDateString('es-CO', options);
    },

    /**
     * Validar formulario
     */
    validateForm(formElement) {
        if (!formElement.checkValidity()) {
            formElement.reportValidity();
            return false;
        }
        return true;
    },

    /**
     * Eliminar registro con confirmación
     */
    deleteRecord(url, redirectUrl = null) {
        this.confirm('Esta acción no se puede deshacer.', async () => {
            try {
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

                const data = await this.fetch(url, {
                    method: 'POST',
                    body: JSON.stringify({ csrf_token: csrfToken })
                });

                this.toast(data.message || 'Eliminado con éxito', 'success');

                if (redirectUrl) {
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } catch (error) {
                console.error('Error al eliminar:', error);
            }
        });
    },

    /**
     * Cargar contenido dinámico
     */
    async loadContent(url, targetElement) {
        try {
            const response = await fetch(url);
            const html = await response.text();

            if (typeof targetElement === 'string') {
                document.querySelector(targetElement).innerHTML = html;
            } else {
                targetElement.innerHTML = html;
            }
        } catch (error) {
            console.error('Error al cargar contenido:', error);
            this.toast('Error al cargar contenido', 'error');
        }
    },

    /**
     * Exportar tabla a CSV
     */
    exportTableToCSV(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        let csv = [];

        // Headers
        const headers = table.querySelectorAll('thead th');
        let headerRow = [];
        headers.forEach(header => {
            headerRow.push(header.textContent.trim());
        });
        csv.push(headerRow.join(','));

        // Rows
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            let rowData = [];
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
            });
            csv.push(rowData.join(','));
        });

        // Download
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();

        this.toast('Exportado con éxito', 'success');
    },

    /**
     * Imprimir elemento
     */
    print(elementId) {
        const element = document.getElementById(elementId);
        const printWindow = window.open('', '', 'height=600,width=800');

        printWindow.document.write('<html><head><title>Imprimir</title>');
        printWindow.document.write('<link rel="stylesheet" href="/css/output.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(element.innerHTML);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    },

    /**
     * Inicializar datepicker en un elemento
     */
    initDatepicker(selector, options = {}) {
        const defaultOptions = {
            dateFormat: 'Y-m-d',
            locale: Spanish,
            allowInput: true,
            ...options
        };

        return flatpickr(selector, defaultOptions);
    },

    /**
     * Inicializar select mejorado
     */
    initSelect(selector, options = {}) {
        const defaultOptions = {
            searchEnabled: true,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay opciones disponibles',
            ...options
        };

        return new Choices(selector, defaultOptions);
    }
};

// Auto-cerrar flash messages después de 5 segundos
document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });

    // Inicializar tooltips y popovers de Flowbite si está disponible
    if (typeof flowbite !== 'undefined' && flowbite.initFlowbite) {
        flowbite.initFlowbite();
    }
});

// Manejo de errores global
window.addEventListener('error', (event) => {
    console.error('Error global:', event.error);

    if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
        // En producción, enviar error al servidor
        // App.fetch('/api/log-error', {
        //     method: 'POST',
        //     body: JSON.stringify({
        //         error: event.error.message,
        //         stack: event.error.stack,
        //         url: window.location.href
        //     })
        // }).catch(() => {});
    }
});

// Prevenir múltiples envíos de formularios
document.addEventListener('submit', (e) => {
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');

    if (submitButton && !submitButton.disabled) {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');

        // Rehabilitar después de 3 segundos por si falla
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }, 3000);
    }
});

// Logout con confirmación
document.addEventListener('DOMContentLoaded', () => {
    const logoutLinks = document.querySelectorAll('a[href="/logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            App.confirm('¿Desea cerrar sesión?', () => {
                window.location.href = '/logout';
            });
        });
    });
});

console.log('📦 App bundle loaded successfully');
