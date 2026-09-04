# Greativa HR Portal 🚀

Plateforme RH complète — Laravel 11 (Backend) + Vue 3 (Frontend).

---

## 📦 Structure du projet

```
greativa-hr/
├── backend/          # API Laravel 11
└── frontend/         # SPA Vue 3 + Vite + TailwindCSS
```

---

## ⚙️ Backend — Démarrage

### Prérequis

- PHP >= 8.2
- Composer
- MySQL 8

### Installation

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

### Configuration `.env`

Mettez à jour les variables :

```env
DB_HOST=127.0.0.1
DB_DATABASE=greativa_hr
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@greativaconsulting.com
MAIL_FROM_NAME="Greativa HR"

FRONTEND_URL=http://localhost:5173
```

### Base de données & Seed

```bash
php artisan migrate --seed
```

Cela crée :

- Un admin par défaut : `admin@greativa.ma` / `password`
- 5 départements
- 3 offres de test

### Démarrage du serveur

```bash
php artisan serve
# → http://localhost:8000
```

---

## 🎨 Frontend — Démarrage

### Prérequis

- Node.js >= 18

### Installation

```bash
cd frontend
npm install
```

### Configuration `.env`

```env
VITE_API_URL=http://localhost:8000/api
VITE_DEFAULT_LOCALE=fr
```

### Démarrage

```bash
npm run dev
# → http://localhost:5173
```

---

## 🔑 Accès Admin

| URL                                 | Credentials                      |
| ----------------------------------- | -------------------------------- |
| `http://localhost:5173/admin/login` | `admin@greativa.ma` / `password` |

---

## 📋 Fonctionnalités

### Côté public

- ✅ Page d'accueil avec stats animées, offres récentes, processus
- ✅ Listing des offres avec filtres (département, contrat, recherche, pagination)
- ✅ Détail d'une offre avec partage social
- ✅ Formulaire de candidature avec upload CV (drag-drop, PDF, 5 Mo max)
- ✅ Email de confirmation automatique
- ✅ Pages Guide, FAQ, À Propos, Contact
- ✅ Interface bilingue Français / Anglais

### Côté admin

- ✅ Authentification sécurisée (Sanctum tokens)
- ✅ Dashboard avec KPI, statuts, entretiens à venir
- ✅ Gestion des candidatures (workflow Nouveau → Embauché / Rejeté)
- ✅ Notes internes sur candidatures et candidats
- ✅ Export Excel des candidatures
- ✅ Gestion complète des offres (CRUD, toggle publish, archive)
- ✅ Gestion des départements
- ✅ Planification des entretiens avec résultats
- ✅ Archives avec restauration
- ✅ Téléchargement des CV

---

## 🔗 Routes API principales

| Méthode | Endpoint                        | Description                   |
| ------- | ------------------------------- | ----------------------------- |
| `GET`   | `/api/offres`                   | Liste des offres publiées     |
| `GET`   | `/api/offres/{id}`              | Détail d'une offre            |
| `POST`  | `/api/candidatures`             | Soumettre une candidature     |
| `POST`  | `/api/auth/login`               | Login admin                   |
| `GET`   | `/api/stats/overview`           | Dashboard stats (auth)        |
| `GET`   | `/api/candidatures`             | Liste des candidatures (auth) |
| `PATCH` | `/api/candidatures/{id}/statut` | Changer le statut (auth)      |
| `GET`   | `/api/candidatures/export`      | Export Excel (auth)           |

---

## 🎨 Design System

| Token          | Valeur    |
| -------------- | --------- |
| `brand`        | `#F05728` |
| `brand-hover`  | `#D9431A` |
| `bg-main`      | `#F7F7F7` |
| `text-primary` | `#000000` |
| Police titres  | DM Sans   |
| Police texte   | Inter     |

---

## 📁 Structure Frontend

```
src/
├── assets/
│   └── main.css          # Design system complet
├── components/
│   └── public/
│       ├── NavBar.vue     # Navigation flottante
│       ├── FooterBar.vue  # Footer sombre
│       └── JobCard.vue    # Carte offre d'emploi
├── i18n/
│   ├── fr.json            # Traductions françaises
│   └── en.json            # Traductions anglaises
├── router/
│   └── index.js           # Routes avec guards
├── services/
│   └── api.js             # Axios + interceptors
├── stores/
│   ├── authStore.js        # Auth Pinia
│   ├── offresStore.js      # Offres Pinia
│   └── candidaturesStore.js # Candidatures Pinia
└── views/
    ├── public/
    │   ├── PublicLayout.vue
    │   ├── HomeView.vue
    │   ├── OffresView.vue
    │   ├── OffreDetailView.vue
    │   ├── CandidatureView.vue
    │   ├── ContactView.vue
    │   ├── FaqView.vue
    │   ├── GuideView.vue
    │   └── AProposView.vue
    └── admin/
        ├── AdminLayout.vue
        ├── AdminLoginView.vue
        ├── DashboardView.vue
        ├── CandidaturesView.vue
        ├── OffresAdminView.vue
        ├── DepartementsView.vue
        ├── EntretiensView.vue
        ├── ArchivesView.vue
        └── CandidatsView.vue
```
