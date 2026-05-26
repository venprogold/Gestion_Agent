[cahier_charge.html](https://github.com/user-attachments/files/28261260/cahier_charge.html)
# Gestion_Agent
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cahier des charges - Gestion des Agents d'Entretien</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
            padding: 40px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
        }
        h1 {
            color: #667eea;
            text-align: center;
            font-size: 28px;
            margin-bottom: 10px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        h2 {
            color: #667eea;
            background: #f0f4ff;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 30px 0 20px 0;
            font-size: 22px;
        }
        h3 {
            color: #764ba2;
            margin: 20px 0 10px 0;
            font-size: 18px;
        }
        .header-info {
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-style: italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .highlight {
            background: #e8f0fe;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-right: 5px;
        }
        .badge-blue {
            background: #2196F3;
        }
        .badge-orange {
            background: #FF9800;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
        }
        .page-break {
            page-break-before: always;
        }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .diagram {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            overflow-x: auto;
            margin: 15px 0;
            border: 1px dashed #ccc;
        }
        .color-green { color: #4CAF50; }
        .color-red { color: #F44336; }
        .color-blue { color: #2196F3; }
        .color-orange { color: #FF9800; }
        .color-purple { color: #9C27B0; }
    </style>
</head>
<body>
<div class="container">
    
    <!-- COUVERTURE -->
    <div style="text-align: center; margin-bottom: 50px;">
        <h1 style="font-size: 36px;">📋 CAHIER DES CHARGES</h1>
        <h2 style="background: none; color: #667eea; font-size: 24px;">Application de Gestion des Agents d'Entretien</h2>
        <h3 style="color: #764ba2;">(Work Study) - Faculté des Sciences</h3>
        <div style="margin: 30px 0;">
        </div>
    </div>

    <div class="page-break"></div>

    <!-- SOMMAIRE -->
    <h2>📑 SOMMAIRE</h2>
    <ul style="list-style: none; padding-left: 0;">
        <li>1. <a href="#section1" style="color: #667eea; text-decoration: none;">Présentation du projet</a></li>
        <li>2. <a href="#section2" style="color: #667eea; text-decoration: none;">Objectifs du projet</a></li>
        <li>3. <a href="#section3" style="color: #667eea; text-decoration: none;">Périmètre fonctionnel</a></li>
        <li>4. <a href="#section4" style="color: #667eea; text-decoration: none;">Modèle Conceptuel de Données (MCD)</a></li>
        <li>5. <a href="#section5" style="color: #667eea; text-decoration: none;">Modèle Logique de Données (MLD)</a></li>
        <li>6. <a href="#section6" style="color: #667eea; text-decoration: none;">Diagramme de classes (UML)</a></li>
        <li>7. <a href="#section7" style="color: #667eea; text-decoration: none;">Diagramme de cas d'utilisation</a></li>
        <li>8. <a href="#section8" style="color: #667eea; text-decoration: none;">Diagramme de séquence</a></li>
        <li>9. <a href="#section9" style="color: #667eea; text-decoration: none;">Diagramme d'activité</a></li>
        <li>10. <a href="#section10" style="color: #667eea; text-decoration: none;">Architecture technique</a></li>
        <li>11. <a href="#section11" style="color: #667eea; text-decoration: none;">Liste des fichiers</a></li>
        <li>12. <a href="#section12" style="color: #667eea; text-decoration: none;">Règles de gestion</a></li>
        <li>13. <a href="#section13" style="color: #667eea; text-decoration: none;">Glossaire</a></li>
    </ul>

    <div class="page-break"></div>

    <!-- SECTION 1 -->
    <h2 id="section1">1. 📌 PRÉSENTATION DU PROJET</h2>
    <div class="highlight">
        <p><strong>Contexte :</strong> La Faculté des Sciences souhaite digitaliser la gestion de ses agents d'entretien (work study) afin de remplacer les processus manuels (papier, tableurs, échanges oraux).</p>
    </div>
    
    <h3>1.1 Porteurs du projet</h3>
    <ul>
        <li><span class="badge">👑</span> Responsable pédagogique : Faculté des Sciences</li>
        <li><span class="badge">💻</span> Développeur : Équipe projet informatique</li>
        <li><span class="badge">👥</span> Utilisateurs finaux : Responsables et agents d'entretien</li>
    </ul>

    <h3>1.2 Description de l'application</h3>
    <p>Application web complète permettant :</p>
    <ul>
        <li>La gestion centralisée des agents d'entretien</li>
        <li>L'assignation et le suivi des tâches</li>
        <li>La gestion des horaires et plannings</li>
        <li>La soumission de rapports avec preuves médias (photos/vidéos)</li>
        <li>La messagerie interne entre agents et responsables</li>
        <li>L'évaluation des performances</li>
        <li>Les statistiques et rapports exportables</li>
    </ul>

    <div class="page-break"></div>

    <!-- SECTION 2 -->
    <h2 id="section2">2. 🎯 OBJECTIFS DU PROJET</h2>
    <div class="highlight">
        <p><strong>Objectif principal :</strong> Offrir une solution numérique complète pour la gestion des agents d'entretien de la Faculté des Sciences.</p>
    </div>
    
    <table>
        <thead>
            <tr><th>Objectif</th><th>Description</th></tr>
        </thead>
        <tbody>
            <tr><td><span class="badge badge-blue">1</span> Centralisation</td><td>Centraliser la gestion des horaires, tâches, rapports et performances</td></tr>
            <tr><td><span class="badge badge-blue">2</span> Suivi temps réel</td><td>Permettre le suivi en temps réel du travail effectué</td></tr>
            <tr><td><span class="badge badge-blue">3</span> Communication</td><td>Faciliter la communication entre agents et responsables</td></tr>
            <tr><td><span class="badge badge-blue">4</span> Statistiques</td><td>Fournir des indicateurs statistiques pour l'optimisation des ressources</td></tr>
            <tr><td><span class="badge badge-blue">5</span> Auto-inscription</td><td>Permettre l'auto-inscription et la validation des comptes</td></tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- SECTION 3 -->
    <h2 id="section3">3. 📋 PÉRIMÈTRE FONCTIONNEL</h2>
    
    <h3>3.1 Acteurs du système</h3>
    <table>
        <thead><tr><th>Acteur</th><th>Rôle</th><th>Fonctionnalités principales</th></tr></thead>
        <tbody>
            <tr><td><span class="badge">👑</span> Responsable</td><td>Gère les agents</td><td>Assignation tâches, validation rapports, statistiques, messagerie</td></tr>
            <tr><td><span class="badge">🧹</span> Agent</td><td>Exécute les tâches</td><td>Soumission rapports, consultation tâches, messagerie</td></tr>
            <tr><td><span class="badge">⚙️</span> Administrateur</td><td>Valide les comptes</td><td>Validation comptes responsables</td></tr>
        </tbody>
    </table>

    <h3>3.2 Modules fonctionnels</h3>
    <table>
        <thead><tr><th>Module</th><th>Fonctionnalités clés</th></tr></thead>
        <tbody>
            <tr><td><span class="badge badge-blue">🔐</span> Authentification</td><td>Inscription, connexion, validation des comptes responsables</td></tr>
            <tr><td><span class="badge badge-blue">👤</span> Profil</td><td>Photo de profil, informations personnelles, secteur</td></tr>
            <tr><td><span class="badge badge-blue">📅</span> Horaires</td><td>CRUD plannings, calendrier, changement de statut</td></tr>
            <tr><td><span class="badge badge-blue">✅</span> Tâches</td><td>CRUD tâches, priorités, statuts, équipements, commentaires</td></tr>
            <tr><td><span class="badge badge-blue">📝</span> Rapports</td><td>Soumission avec photos/vidéos, approbation par responsable</td></tr>
            <tr><td><span class="badge badge-blue">💬</span> Messagerie</td><td>Messages privés, messages groupés, notifications</td></tr>
            <tr><td><span class="badge badge-blue">📊</span> Performances</td><td>Évaluations, notes, feedbacks, heures travaillées</td></tr>
            <tr><td><span class="badge badge-blue">📈</span> Statistiques</td><td>Graphiques, export PDF/Excel, indicateurs</td></tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- SECTION 4 : MCD -->
    <h2 id="section4">4. 🗄️ MODÈLE CONCEPTUEL DE DONNÉES (MCD)</h2>
    
    <div class="diagram">
        <pre style="margin: 0;">
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      MCD - Gestion des Agents                               │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                             │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐            │
│  │    USER      │     │   TASK       │     │  SCHEDULE    │     │   MESSAGE    │            │
│  ├──────────────┤     ├──────────────┤     ├──────────────┤     ├──────────────┤            │
│  │ id           │────<│ assigned_to  │     │ agent_id     │>────│ sender_id    │            │
│  │ username     │     │ assigned_by  │>────│ created_by   │     │ receiver_id  │>───┐       │
│  │ password     │────>│              │     │              │     │              │    │       │
│  │ email        │     └──────────────┘     └──────────────┘     └──────────────┘    │       │
│  │ role         │                                                                   │       │
│  │ telephone    │     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐    │       │
│  │ secteur      │     │ WORK_REPORT  │     │ PERFORMANCE  │     │ NOTIFICATION │    │       │
│  │ profile_photo│     ├──────────────┤     │   REVIEW     │     ├──────────────┤    │       │
│  │ is_approved  │────<│ user_id      │     ├──────────────┤     │ user_id      │>───┘       │
│  │ created_at   │     └──────────────┘     │ agent_id     │>────│              │            │
│  └──────────────┘             │            │ reviewer_id  │>────│              │            │ 
│                               │            └──────────────┘     └──────────────┘            │
│                    ┌──────────┴──────────┐                                                  │
│                    │    REPORT_MEDIA     │                                                  │
│                    ├─────────────────────┤                                                  │
│                    │ report_id           │>──┐                                              │
│                    │ file_name           │   │                                              │
│                    │ file_path           │   │                                              │
│                    │ file_type           │   │                                              │
│                    └─────────────────────┘   │                                              │
│                                              │                                              │
│                                ┌─────────────┴─────────────┐                                │
│                                │      CONVERSATION         │                                │
│                                ├───────────────────────────┤                                │
│                                │ participant1              │>────┐                          │
│                                │ participant2              │>────┼──────────────────────────┘
│                                │ last_message              │     │
│                                │ last_message_time         │     │
│                                └───────────────────────────┘     │
└──────────────────────────────────────────────────────────────────┘
        </pre>
    </div>

    <h3>4.1 Entités et attributs détaillés</h3>
    <table>
        <thead><tr><th>Entité</th><th>Attributs</th></tr></thead>
        <tbody>
            <tr><td><span class="badge">👤</span> USER</td><td>id, username, password, email, role, telephone, secteur, profile_photo, is_approved, created_at</td></tr>
            <tr><td><span class="badge">✅</span> TASK</td><td>id, title, description, equipment_needed, priority, status, assigned_to, assigned_by, start_date, due_date, completed_date, estimated_hours, actual_hours, notes</td></tr>
            <tr><td><span class="badge">📅</span> SCHEDULE</td><td>id, agent_id, title, description, start_datetime, end_datetime, status, created_by</td></tr>
            <tr><td><span class="badge">📝</span> WORK_REPORT</td><td>id, user_id, title, description, work_date, location, hours_spent, status, admin_comment</td></tr>
            <tr><td><span class="badge">📷</span> REPORT_MEDIA</td><td>id, report_id, file_name, file_path, file_type, file_size</td></tr>
            <tr><td><span class="badge">⭐</span> PERFORMANCE_REVIEW</td><td>id, agent_id, reviewer_id, review_date, period_start, period_end, overall_rating, quality_rating, punctuality_rating, comments, feedback</td></tr>
            <tr><td><span class="badge">💬</span> MESSAGE</td><td>id, conversation_id, sender_id, receiver_id, message, is_read</td></tr>
            <tr><td><span class="badge">💭</span> CONVERSATION</td><td>id, participant1, participant2, last_message, last_message_time</td></tr>
            <tr><td><span class="badge">🔔</span> NOTIFICATION</td><td>id, user_id, type, title, message, link, is_read</td></tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- SECTION 5 : MLD -->
    <h2 id="section5">5. 📊 MODÈLE LOGIQUE DE DONNÉES (MLD)</h2>
    
    <div class="code-block">
        <pre>
USER (id, username, password, email, role, telephone, secteur, profile_photo, is_approved, created_at)
PK = id

TASK (id, title, description, equipment_needed, priority, status, assigned_to, assigned_by, start_date, 
      due_date, completed_date, estimated_hours, actual_hours, notes, created_at)
PK = id
FK = assigned_to → USER(id)
FK = assigned_by → USER(id)

SCHEDULE (id, agent_id, title, description, start_datetime, end_datetime, status, created_by)
PK = id
FK = agent_id → USER(id)
FK = created_by → USER(id)

WORK_REPORT (id, user_id, title, description, work_date, location, hours_spent, status, admin_comment, created_at)
PK = id
FK = user_id → USER(id)

REPORT_MEDIA (id, report_id, file_name, file_path, file_type, file_size, created_at)
PK = id
FK = report_id → WORK_REPORT(id)

PERFORMANCE_REVIEW (id, agent_id, reviewer_id, review_date, period_start, period_end, 
                   overall_rating, quality_rating, punctuality_rating, comments, feedback)
PK = id
FK = agent_id → USER(id)
FK = reviewer_id → USER(id)

CONVERSATION (id, participant1, participant2, last_message, last_message_time, created_at, updated_at)
PK = id
FK = participant1 → USER(id)
FK = participant2 → USER(id)

MESSAGE (id, conversation_id, sender_id, receiver_id, message, is_read, created_at)
PK = id
FK = conversation_id → CONVERSATION(id)
FK = sender_id → USER(id)
FK = receiver_id → USER(id)

NOTIFICATION (id, user_id, type, title, message, link, is_read, created_at)
PK = id
FK = user_id → USER(id)
        </pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 6 : DIAGRAMME DE CLASSES -->
    <h2 id="section6">6. 📐 DIAGRAMME DE CLASSES (UML)</h2>
    
    <div class="diagram">
        <pre style="margin: 0;">
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           DIAGRAMME DE CLASSES UML                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌─────────────────────┐         ┌─────────────────────┐                        │
│  │        User         │         │        Task         │                        │
│  ├─────────────────────┤         ├─────────────────────┤                        │
│  │ -id: int            │         │ -id: int            │                        │
│  │ -username: string   │         │ -title: string      │                        │
│  │ -password: string   │         │ -description: text  │                        │
│  │ -email: string      │         │ -priority: enum     │                        │
│  │ -role: enum         │         │ -status: enum       │                        │
│  │ -telephone: string  │         │ -start_date: date   │                        │
│  │ -secteur: string    │         │ -due_date: date     │                        │
│  │ -profile_photo: str │         │ -estimated_hours:   │                        │
│  │ -is_approved: bool  │         │ -actual_hours:      │                        │
│  ├─────────────────────┤         ├─────────────────────┤                        │
│  │ +login()            │         │ +create()           │                        │
│  │ +register()         │         │ +updateStatus()     │                        │
│  │ +updateProfile()    │         │ +delete()           │                        │
│  │ +uploadPhoto()      │         │ +addComment()       │                        │
│  └─────────┬───────────┘         └───────────┬─────────┘                        │
│            │                                 │                                  │
│            │ 1                              *│                                  │
│            └─────────────────────────────────┘                                  │
│                         assigned_to / assigned_by                               │
│                                                                                 │
│  ┌─────────────────────┐         ┌─────────────────────┐                        │
│  │     WorkReport      │         │    Schedule         │                        │
│  ├─────────────────────┤         ├─────────────────────┤                        │
│  │ -id: int            │         │ -id: int            │                        │
│  │ -title: string      │         │ -title: string      │                        │
│  │ -description: text  │         │ -description: text  │                        │
│  │ -work_date: date    │         │ -start_datetime:    │                        │
│  │ -hours_spent: dec   │         │ -end_datetime:      │                        │
│  │ -status: enum       │         │ -status: enum       │                        │
│  ├─────────────────────┤         └─────────────────────┘                        │
│  │ +submit()           │                                                        │
│  │ +addMedia()         │                                                        │
│  │ +approve()          │                                                        │
│  └─────────┬───────────┘                                                        │
│            │                                                                    │
│            │ 1                                                                  │
│            └──────────┐                                                         │
│                       │ *                                                       │
│  ┌────────────────────┴────┐                                                    │
│  │      ReportMedia        │                                                    │
│  ├─────────────────────────┤                                                    │
│  │ -file_name: string      │                                                    │
│  │ -file_path: string      │                                                    │
│  │ -file_type: enum        │                                                    │
│  │ -file_size: int         │                                                    │
│  └─────────────────────────┘                                                    │
│                                                                                 │
│  ┌─────────────────────┐         ┌─────────────────────┐                        │
│  │    Conversation     │         │      Message        │                        │
│  ├─────────────────────┤         ├─────────────────────┤                        │
│  │ -id: int            │1       *│ -id: int            │                        │
│  │ -participant1: int  │◄────────│ -message: text      │                        │
│  │ -participant2: int  │         │ -is_read: bool      │                        │
│  │ -last_message: text │         └─────────────────────┘                        │
│  └─────────────────────┘                                                        │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
        </pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 7 : DIAGRAMME DE CAS D'UTILISATION -->
    <h2 id="section7">7. 🎭 DIAGRAMME DE CAS D'UTILISATION</h2>
    
    <div class="diagram">
        <pre style="margin: 0;">
┌──────────────────────────────────────────────────────────────────────────────────┐
│                    DIAGRAMME DE CAS D'UTILISATION                                │
├──────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│                                    ┌─────────────────────┐                       │
│                                    │   Gestion des       │                       │
│                                    │   Agents d'Entretien│                       │
│                                    └──────────┬──────────┘                       │
│                                               │                                  │
│                    ┌──────────────────────────┼──────────────────────────┐       │
│                    │                          │                          │       │
│                    ▼                          ▼                          ▼       │
│            ┌───────────────┐          ┌───────────────┐          ┌───────────────┐
│            │  Responsable  │          │    Agent      │          │ Administrateur│
│            └───────────────┘          └───────────────┘          └───────────────┘
│                    │                          │                          │       │
│       ┌────────────┼──────────────────────────┼──────────────────────────┼────┐  │
│       │            │                          │                          │    │  │
│       ▼            ▼                          ▼                          ▼    ▼  │
│  ┌─────────────────────────────────────────────────────────────────────────┐     │
│  │                          CAS D'UTILISATION                              │     │
│  ├─────────────────────────────────────────────────────────────────────────┤     │
│  │                                                                         │     │
│  │  RESPONSABLE :                                                          │     │
│  │  ├── UC1 : S'authentifier                                               │     │
│  │  ├── UC2 : Gérer son profil (photo, infos)                              │     │
│  │  ├── UC3 : Créer/modifier/supprimer des horaires                        │     │
│  │  ├── UC4 : Assigner des tâches aux agents                               │     │
│  │  ├── UC5 : Suivre l'avancement des tâches                               │     │
│  │  ├── UC6 : Évaluer les performances des agents                          │     │
│  │  ├── UC7 : Consulter les rapports des agents                            │     │
│  │  ├── UC8 : Approuver/rejeter les rapports                               │     │
│  │  ├── UC9 : Visualiser les statistiques (graphiques)                     │     │
│  │  ├── UC10: Exporter les rapports (PDF/Excel)                            │     │
│  │  ├── UC11: Envoyer des messages (individuels/groupés)                   │     │
│  │  ├── UC12: Valider les nouveaux comptes responsables                    │     │
│  │  └── UC13: Consulter les notifications                                  │     │
│  │                                                                         │     │
│  │  AGENT :                                                                │     │
│  │  ├── UC14 : S'authentifier                                              │     │
│  │  ├── UC15 : Gérer son profil (photo, infos)                             │     │
│  │  ├── UC16 : Consulter ses horaires                                      │     │
│  │  ├── UC17 : Consulter ses tâches                                        │     │
│  │  ├── UC18 : Mettre à jour le statut des tâches                          │     │ 
│  │  ├── UC19 : Soumettre un rapport de travail (photos/vidéos)             │     │
│  │  ├── UC20 : Consulter l'historique de ses rapports                      │     │
│  │  ├── UC21 : Enregistrer ses heures de travail                           │     │
│  │  ├── UC22 : Consulter ses évaluations                                   │     │
│  │  ├── UC23 : Communiquer (messages)                                      │     │
│  │  ├── UC24 : Signaler un problème sur une tâche                          │     │
│  │  └── UC25 : Consulter les notifications                                 │     │
│  │                                                                         │     │
│  │  ADMINISTRATEUR :                                                       │     │
│  │  ├── UC26 : Valider les comptes responsables                            │     │
│  │  └── UC27 : Gérer les utilisateurs                                      │     │
│  │                                                                         │     │
│  │  COMMUNS :                                                              │     │
│  │  ├── UC28 : S'inscrire (auto-inscription)                               │     │
│  │  └── UC29 : Se déconnecter                                              │     │
│  │                                                                         │     │
│  └─────────────────────────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────────────────────────┘
        </pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 8 : DIAGRAMME DE SÉQUENCE -->
    <h2 id="section8">8. ⚡ DIAGRAMME DE SÉQUENCE (Assignation d'une tâche)</h2>
    
    <div class="diagram">
        <pre style="margin: 0;">
┌─────────────────────────────────────────────────────────────────────────────────┐
│                    DIAGRAMME DE SÉQUENCE - Assignation d'une tâche              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌───────────┐       ┌──────────────┐       ┌──────────────┐       ┌──────────┐ │
│  │Responsable│       │   Interface  │       │   Contrôleur │       │  Base de │ │
│  │           │       │   (Browser)  │       │   (PHP)      │       │ données  │ │
│  └────┬──────┘       └──────┬───────┘       └──────┬───────┘       └────┬─────┘ │
│       │                     │                      │                    │       │
│       │1. Remplit formulaire│                      │                    │       │
│       │────────────────────>│                      │                    │       │
│       │                     │                      │                    │       │
│       │ 2. Submit form      │                      │                    │       │
│       │────────────────────>│                      │                    │       │
│       │                     │                      │                    │       │
│       │                     │ 3. POST /tasks.php   │                    │       │
│       │                     │─────────────────────>│                    │       │
│       │                     │                      │                    │       │
│       │                     │                      │ 4.INSERT INTO tasks│       │
│       │                     │                      │───────────────────>│       │
│       │                     │                      │                    │       │
│       │                     │                      │ 5.Tâche créée      │       │
│       │                     │                      │<───────────────────│       │
│       │                     │                      │                    │       │
│       │                     │                      │ 6.INSERT INTO      │       │
│       │                     │                      │   notifications    │       │
│       │                     │                      │───────────────────>│       │
│       │                     │                      │                    │       │
│       │                     │ 7. Redirection       │                    │       │
│       │                     │<─────────────────────│                    │       │
│       │                     │                      │                    │       │
│       │ 8. Affiche la liste │                      │                    │       │
│       │<────────────────────│                      │                    │       │
│       │                     │                      │                    │       │
│       │                     │                      │9.Notification Agent│       │
│       │                     │                      │───────────────────>│       │
│       │                     │                      │                    │       │
└───────┴─────────────────────┴──────────────────────┴────────────────────┴───────┘
        </pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 9 : DIAGRAMME D'ACTIVITÉ -->
    <h2 id="section9">9. 🔄 DIAGRAMME D'ACTIVITÉ (Validation d'un rapport)</h2>
    
    <div class="diagram">
        <pre style="margin: 0;">
┌─────────────────────────────────────────────────────────────────────────────────┐
│               DIAGRAMME D'ACTIVITÉ - Validation d'un rapport                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│                              ┌─────────────┐                                    │
│                              │   Début     │                                    │
│                              └──────┬──────┘                                    │
│                                     │                                           │
│                                     ▼                                           │
│                              ┌─────────────┐                                    │
│                              │ Agent crée  │                                    │
│                              │ un rapport  │                                    │
│                              │ avec médias │                                    │
│                              └──────┬──────┘                                    │
│                                     │                                           │
│                                     ▼                                           │
│                              ┌─────────────┐                                    │
│                              │ Rapport     │                                    │
│                              │ soumis      │                                    │
│                              │ (submitted) │                                    │
│                              └──────┬──────┘                                    │
│                                     │                                           │
│                                     ▼                                           │
│                              ┌───────────────┐                                  │
│                              │ Notification  │                                  │
│                              │ au(x)         │                                  │
│                              │ responsable(s)│                                  │
│                              └──────┬────────┘                                  │
│                                     │                                           │
│                                     ▼                                           │
│                              ┌─────────────┐                                    │
│                              │ Responsable │                                    │
│                              │ consulte le │                                    │
│                              │ rapport     │                                    │
│                              └──────┬──────┘                                    │
│                                     │                                           │
│                          ┌──────────┴──────────┐                                │
│                          │                     │                                │
│                          ▼                     ▼                                │
│                   ┌─────────────┐       ┌─────────────┐                         │
│                   │ Rapport     │       │ Rapport     │                         │
│                   │ approuvé    │       │ rejeté      │                         │
│                   └──────┬──────┘       └──────┬──────┘                         │
│                          │                     │                                │
│                          ▼                     ▼                                │
│                   ┌─────────────┐       ┌─────────────┐                         │
│                   │ Status =    │       │ Status =    │                         │
│                   │ "approved"  │       │ "rejected"  │                         │
│                   └──────┬──────┘       └──────┬──────┘                         │
│                          │                     │                                │
│                          ▼                     ▼                                │
│                   ┌─────────────┐       ┌─────────────┐                         │
│                   │ Notification│       │ Notification│                         │
│                   │ à l'agent   │       │ à l'agent   │                         │
│                   │ + commentaire│      │ + motif     │                         │
│                   └──────┬──────┘       └──────┬──────┘                         │
│                          │                     │                                │
│                          └──────────┬──────────┘                                │
│                                     │                                           │
│                                     ▼                                           │
│                              ┌─────────────┐                                    │
│                              │    Fin      │                                    │
│                              └─────────────┘                                    │
└─────────────────────────────────────────────────────────────────────────────────┘
        </pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 10 : ARCHITECTURE TECHNIQUE -->
    <h2 id="section10">10. 🖥️ ARCHITECTURE TECHNIQUE</h2>
    
    <h3>10.1 Stack technique</h3>
    <table>
        <thead><tr><th>Composant</th><th>Technologie</th><th>Version</th></tr></thead>
        <tbody>
            <tr><td><span class="badge">🎯</span> Backend</td><td>PHP</td><td>8.x</td></tr>
            <tr><td><span class="badge">🗄️</span> Base de données</td><td>MySQL / MariaDB</td><td>10.4+</td></tr>
            <tr><td><span class="badge">🎨</span> Frontend</td><td>HTML5, CSS3, JavaScript</td><td>-</td></tr>
            <tr><td><span class="badge">📊</span> Graphiques</td><td>Chart.js</td><td>4.4+</td></tr>
            <tr><td><span class="badge">🌐</span> Serveur</td><td>Apache (XAMPP/WAMP)</td><td>2.4+</td></tr>
            <tr><td><span class="badge">🔐</span> Authentification</td><td>Sessions PHP + Bcrypt</td><td>-</td></tr>
        </tbody>
    </table>

    <h3>10.2 Structure des dossiers</h3>
    <div class="code-block">
        <pre>
gestion_agents/
├── .htaccess
├── index.php
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── profile.php
├── responsable_dashboard.php
├── agent_dashboard.php
├── schedules.php
├── my_schedules.php
├── tasks.php
├── my_tasks.php
├── submit_report.php
├── my_reports.php
├── view_reports.php
├── messages.php
├── send_message.php
├── send_group_message.php
├── get_messages.php
├── mark_messages_read.php
├── notifications.php
├── performance.php
├── my_performance.php
├── reports.php
├── work_log.php
├── admin_approve.php
├── check_delayed_tasks.php
├── cron_tasks.php
├── create_admin.php
├── config/
│   ├── database.php
│   └── upload.php
├── css/
│   └── style.css
├── sql/
│   └── database.sql
├── uploads/
│   ├── profiles/
│   └── reports/
└── images/
    └── default-avatar.png
        </pre>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 11 : LISTE DES FICHIERS -->
    <h2 id="section11">11. 📁 LISTE DES FICHIERS ET LEURS RÔLES</h2>
    
    <table>
        <thead>
            <tr><th>Fichier</th><th>Rôle</th><th>Accès</th></tr>
        </thead>
        <tbody>
            <tr><td><span class="badge">🔐</span> login.php</td><td>Authentification des utilisateurs</td><td>Public</td></tr>
            <tr><td><span class="badge">📝</span> register.php</td><td>Auto-inscription (gestion validation responsables)</td><td>Public</td></tr>
            <tr><td><span class="badge">👤</span> profile.php</td><td>Gestion du profil et photo</td><td>Connecté</td></tr>
            <tr><td><span class="badge">👑</span> responsable_dashboard.php</td><td>Tableau de bord responsable</td><td>Responsable</td></tr>
            <tr><td><span class="badge">🧹</span> agent_dashboard.php</td><td>Tableau de bord agent</td><td>Agent</td></tr>
            <tr><td><span class="badge">📅</span> schedules.php</td><td>Gestion des horaires (CRUD)</td><td>Responsable</td></tr>
            <tr><td><span class="badge">📅</span> my_schedules.php</td><td>Consultation horaires agent</td><td>Agent</td></tr>
            <tr><td><span class="badge">✅</span> tasks.php</td><td>Gestion des tâches (CRUD)</td><td>Responsable</td></tr>
            <tr><td><span class="badge">✅</span> my_tasks.php</td><td>Consultation tâches agent</td><td>Agent</td></tr>
            <tr><td><span class="badge">📝</span> submit_report.php</td><td>Soumission rapport avec médias</td><td>Agent</td></tr>
            <tr><td><span class="badge">📋</span> my_reports.php</td><td>Historique rapports agent</td><td>Agent</td></tr>
            <tr><td><span class="badge">👁️</span> view_reports.php</td><td>Gestion rapports (responsable)</td><td>Responsable</td></tr>
            <tr><td><span class="badge">💬</span> messages.php</td><td>Messagerie (individuelle/groupée)</td><td>Connecté</td></tr>
            <tr><td><span class="badge">🔔</span> notifications.php</td><td>Centre de notifications</td><td>Connecté</td></tr>
            <tr><td><span class="badge">⭐</span> performance.php</td><td>Évaluations (responsable)</td><td>Responsable</td></tr>
            <tr><td><span class="badge">⭐</span> my_performance.php</td><td>Consultation évaluations agent</td><td>Agent</td></tr>
            <tr><td><span class="badge">⏱️</span> work_log.php</td><td>Enregistrement heures travaillées</td><td>Agent</td></tr>
            <tr><td><span class="badge">📊</span> reports.php</td><td>Statistiques et graphiques</td><td>Responsable</td></tr>
            <tr><td><span class="badge">✅</span> admin_approve.php</td><td>Validation comptes responsables</td><td>Responsable</td></tr>
            <tr><td><span class="badge">🕐</span> check_delayed_tasks.php</td><td>Détection tâches en retard</td><td>Cron</td></tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- SECTION 12 : RÈGLES DE GESTION -->
    <h2 id="section12">12. 📜 RÈGLES DE GESTION</h2>
    
    <h3>12.1 Authentification</h3>
    <div class="highlight">
        <ul>
            <li><span class="badge">RG1</span> Tout utilisateur doit s'authentifier pour accéder à l'application</li>
            <li><span class="badge">RG2</span> Les mots de passe sont hashés (bcrypt)</li>
            <li><span class="badge">RG3</span> Un compte responsable doit être approuvé par un responsable existant</li>
            <li><span class="badge">RG4</span> Un agent peut s'inscrire librement</li>
        </ul>
    </div>

    <h3>12.2 Tâches</h3>
    <div class="highlight">
        <ul>
            <li><span class="badge">RG5</span> Une tâche ne peut être assignée qu'à un agent existant</li>
            <li><span class="badge">RG6</span> Une tâche peut avoir 5 statuts : à faire, en cours, terminée, en retard, annulée</li>
            <li><span class="badge">RG7</span> Une tâche en retard est automatiquement détectée et notifiée</li>
            <li><span class="badge">RG8</span> Seul l'agent assigné peut modifier le statut de sa tâche</li>
        </ul>
    </div>

    <h3>12.3 Rapports</h3>
    <div class="highlight">
        <ul>
            <li><span class="badge">RG9</span> Un rapport peut contenir plusieurs photos/vidéos</li>
            <li><span class="badge">RG10</span> Un rapport soumis doit être approuvé par un responsable</li>
            <li><span class="badge">RG11</span> Les fichiers uploadés sont limités à 10 Mo pour les images, 50 Mo pour les vidéos</li>
        </ul>
    </div>

    <h3>12.4 Messagerie</h3>
    <div class="highlight">
        <ul>
            <li><span class="badge">RG12</span> Un agent peut envoyer des messages aux responsables et aux autres agents</li>
            <li><span class="badge">RG13</span> Un responsable peut envoyer des messages individuels ou groupés</li>
            <li><span class="badge">RG14</span> Les messages non lus sont indiqués par un badge</li>
        </ul>
    </div>

    <h3>12.5 Notifications</h3>
    <div class="highlight">
        <ul>
            <li><span class="badge">RG15</span> Toute action importante génère une notification</li>
            <li><span class="badge">RG16</span> Les notifications peuvent être marquées comme lues</li>
        </ul>
    </div>

    <div class="page-break"></div>

    <!-- SECTION 13 : GLOSSAIRE -->
    <h2 id="section13">13. 📖 GLOSSAIRE</h2>
    
    <div class="highlight">
        <ul>
            <li><strong>Work study</strong> : Étudiant travaillant à temps partiel pour l'université</li>
            <li><strong>Agent</strong> : Utilisateur qui exécute les tâches d'entretien</li>
            <li><strong>Responsable</strong> : Utilisateur qui gère les agents et les tâches</li>
            <li><strong>Tâche</strong> : Action spécifique assignée à un agent avec une échéance</li>
            <li><strong>Planning</strong> : Horaire de travail prédéfini pour un agent</li>
            <li><strong>Rapport</strong> : Compte-rendu de travail avec preuves médias</li>
            <li><strong>Notification</strong> : Alerte envoyée à un utilisateur</li>
            <li><strong>Évaluation</strong> : Note et feedback donnés par un responsable</li>
            <li><strong>MCD</strong> : Modèle Conceptuel de Données</li>
            <li><strong>MLD</strong> : Modèle Logique de Données</li>
            <li><strong>UML</strong> : Unified Modeling Language</li>
            <li><strong>PDO</strong> : PHP Data Objects</li>
            <li><strong>CRUD</strong> : Create, Read, Update, Delete</li>
        </ul>
    </div>

    <!-- ANNEXES -->
    <h2>📎 ANNEXES</h2>
    
    <h3>Annexe 1 : Codes des statuts</h3>
    <table>
        <thead><tr><th>Statut</th><th>Code</th><th>Couleur</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>À faire</td><td>a_faire</td><td style="background:#2196F3; color:white;">🔵</td><td>Tâche assignée non commencée</td></tr>
            <tr><td>En cours</td><td>en_cours</td><td style="background:#FF9800; color:white;">🟠</td><td>Tâche en cours d'exécution</td></tr>
            <tr><td>Terminée</td><td>terminee</td><td style="background:#4CAF50; color:white;">🟢</td><td>Tâche complétée</td></tr>
            <tr><td>En retard</td><td>en_retard</td><td style="background:#F44336; color:white;">🔴</td><td>Tâche non terminée après la date d'échéance</td></tr>
            <tr><td>Annulée</td><td>annulee</td><td style="background:#9E9E9E; color:white;">⚪</td><td>Tâche annulée</td></tr>
        </tbody>
    </table>

    <h3>Annexe 2 : Codes des priorités</h3>
    <table>
        <thead><tr><th>Priorité</th><th>Code</th><th>Couleur</th></tr></thead>
        <tbody>
            <tr><td>Basse</td><td>basse</td><td style="color:#4CAF50;">🟢</td></tr>
            <tr><td>Moyenne</td><td>moyenne</td><td style="color:#FF9800;">🟠</td></tr>
            <tr><td>Haute</td><td>haute</td><td style="color:#F44336;">🔴</td></tr>
            <tr><td>Urgente</td><td>urgente</td><td style="color:#9C27B0;">🟣</td></tr>
        </tbody>
    </table>
</div>
</body>
</html>
