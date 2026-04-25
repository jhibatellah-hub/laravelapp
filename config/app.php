<?php

return [
    // App
    'app' => [
        'subtitle' => 'Gestion médicale',
        'welcome'  => 'Bonjour',
        'cancel'   => 'Annuler',
        'save'     => 'Enregistrer',
    ],

    // Navigation
    'nav' => [
        'main'         => 'PRINCIPAL',
        'admin'        => 'ADMINISTRATION',
        'dashboard'    => 'Tableau de bord',
        'appointments' => 'Rendez-vous',
        'patients'     => 'Patients',
        'services'     => 'Services',
        'reports'      => 'Rapports',
        'settings'     => 'Paramètres',
    ],

    // Auth
    'auth' => [
        'login'         => 'Connexion',
        'logout'        => 'Déconnexion',
        'register'      => 'S\'inscrire',
        'email'         => 'Adresse e-mail',
        'password'      => 'Mot de passe',
        'forgot'        => 'Oublié ?',
        'welcome_back'  => 'Bienvenue !',
        'login_subtitle'=> 'Connectez-vous à votre espace',
        'no_account'    => 'Pas encore de compte ?',
        'demo_accounts' => 'Comptes de démonstration',
        'failed'        => 'Identifiants incorrects.',
    ],

    // Dashboard
    'dashboard' => [
        'total_appointments' => 'Total rendez-vous',
        'pending'            => 'En attente',
        'confirmed'          => 'Confirmés',
        'patients'           => 'Patients actifs',
        'to_confirm'         => 'À confirmer',
        'this_month'         => 'Ce mois',
        'good'               => 'Bonne santé',
        'active'             => 'Actifs',
        'upcoming'           => 'Prochains rendez-vous',
        'no_upcoming'        => 'Aucun rendez-vous à venir',
        'see_all'            => 'Voir tous',
        'last_7_days'        => 'Activité (7 derniers jours)',
        'appointments_per_day'=> 'Rendez-vous par jour',
    ],

    // Appointments
    'appointments' => [
        'new'                 => 'Nouveau RDV',
        'manage'              => 'Gérer les rendez-vous',
        'create_title'        => 'Nouveau rendez-vous',
        'edit_title'          => 'Modifier le rendez-vous',
        'patient'             => 'Patient',
        'doctor'              => 'Médecin',
        'date'                => 'Date',
        'time'                => 'Heure',
        'service'             => 'Service',
        'status'              => 'Statut',
        'actions'             => 'Actions',
        'notes'               => 'Notes',
        'notes_placeholder'   => 'Observations cliniques...',
        'select_patient'      => 'Sélectionner un patient',
        'select_doctor'       => 'Sélectionner un médecin',
        'select_service'      => 'Sélectionner un service',
        'all_statuses'        => 'Tous les statuts',
        'search_placeholder'  => 'Rechercher patient, médecin...',
        'confirm'             => 'Confirmer le RDV',
        'edit'                => 'Modifier',
        'cancel'              => 'Annuler',
        'cancel_btn'          => 'Confirmer l\'annulation',
        'cancel_confirm_title'=> 'Annuler le rendez-vous ?',
        'cancel_confirm_msg'  => 'Êtes-vous sûr de vouloir annuler le rendez-vous de :name ?',
        'none'                => 'Aucun rendez-vous trouvé',
        'time_not_available'  => 'Ce créneau n\'est pas disponible',
        'created_success'     => 'Rendez-vous créé avec succès. Un email de confirmation a été envoyé.',
        'updated_success'     => 'Rendez-vous mis à jour avec succès.',
        'cancelled_success'   => 'Rendez-vous annulé avec succès.',
        'showing'             => 'Affichage :from–:to sur :total',
        'status' => [
            'pending'   => 'En attente',
            'confirmed' => 'Confirmé',
            'cancelled' => 'Annulé',
            'completed' => 'Terminé',
        ],
    ],

    // Roles
    'admin'   => 'Administrateur',
    'doctor'  => 'Médecin',
    'patient' => 'Patient',
];