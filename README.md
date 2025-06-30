# SortirPointCom 🌍

SortirPointCom est une application Symfony moderne et interactive permettant de gérer et organiser des sorties entre stagiaires. Elle offre une interface intuitive et des fonctionnalités avancées pour rendre la planification des événements simple et agréable.

---

## 🛠️ Développement

### Contributeurs
- **Lénaïc Barbier**  
  Développeur principal, en charge de l'architecture, des fonctionnalités principales et des interfaces utilisateur.  
  [GitLab](https://gitlab.com/lenaicb22) |
- **Florian HEUZE**  
  Développeur principal, en charge de l'architecture, des fonctionnalités principales et des interfaces utilisateur.  
  [GitLab](https://gitlab.com/flo2167) | [LinkedIn](https://fr.linkedin.com/in/florian-heuz%C3%A9)
- **Dorian PESCE**  
  Développeur principal, en charge de l'architecture, des fonctionnalités principales et des interfaces utilisateur.  
  [GitLab](https://gitlab.com/Phyrios) | [LinkedIn](https://fr.linkedin.com/in/dorian-pesce-1aa31526a)
---

## 🚀 Fonctionnalités

- **Gestion des événements :**
  - Création, modification et annulation d'événements.
  - Suivi des états d'événements : `REGISTRATION OPEN`, `REGISTRATION CLOSED`, `ONGOING`, `FINISHED`.
  - Possibilité de fournir un motif d'annulation.

- **Système de notification :**
  - Envoi d'e-mails automatiques pour la confirmation d'inscription et les notifications importantes.

- **Système de langue :**
  - Interface multilingue : prise en charge du français et de l'anglais.
  - Sélecteur de langue interactif.

- **Authentification et sécurité :**
  - Inscription et connexion sécurisées.
  - Gestion des rôles utilisateurs (Admin/Utilisateur).

- **Administration :**
  - Tableau de bord pour les administrateurs.
  - Gestion des utilisateurs et des événements.

- **Responsive Design :**
  - Interface optimisée pour les mobiles, tablettes et ordinateurs.
  - Design moderne utilisant Tailwind CSS.

- **Worker automatique :**
  - Mise à jour des états des événements grâce à des workers Symfony Messenger.
  - Gestion des files d'attente pour l'envoi des e-mails et autres tâches asynchrones.

- **Intégration API :**
  - API pour gérer les événements et utilisateurs.

---

## 🖥️ Installation

### Prérequis

- **PHP** : Version >= 8.1
- **Symfony CLI**
- **Composer**
- **Node.js** et **npm** ou **Yarn**
- **Base de données** : MySQL

### Étapes d'installation

1. **Clone du dépôt**
   ```
   git clone https://gitlab.com/lenaicb22/sortirPointCom.git
   cd sortirPointCom
   ```

2. **Installation des dépendances backend**
   ```
   composer install
   ```

3. **Installation des dépendances frontend**
   ```
   npm install
   ```

4. **Configuration de l'environnement**
   - Créez un fichier `.env.local` et configurez vos paramètres :
     ```
     DATABASE_URL=mysql://username:password@127.0.0.1:3306/sortirpointcom
     MAILER_DSN=smtp://user:password@smtp.gmail.com:587
     ```

5. **Migrations et fixtures**
   - Exécutez les migrations :
     ```
     php bin/console doctrine:migrations:migrate
     ```
   - Chargez les fixtures (données d'exemple) :
     ```
     php bin/console doctrine:fixtures:load
     ```

6. **Compilation des assets**
   ```
   npm run build
   ```

7. **Lancer le serveur**
   ```
   symfony server:start
    ```

---

## 📚 Utilisation / Développement

### Mode développement

1. **Lancer le serveur de développement Symfony :**
   ```
   symfony server:start
   ```
2. **Lancer le watcher pour les assets Tailwind :**
   ```
   npm run watch
   ```
3. **Lancer les workers pour Symfony Messenger :**
   ```
   php bin/console messenger:consume async -vv
   ```

### Scripts utiles
- **Lancer le serveur et les workers simultanément :**
  ```
  npm run launch
  ```
- **Compiler les assets pour la production :**
  ```
  npm run build
  ```

---

## ✨ Fonctionnalités détaillées

1. **Gestion des événements**
   - Création, modification, et annulation avec un formulaire interactif.
   - Suivi des états des événements avec un worker asynchrone :
     - `REGISTRATION OPEN` : Inscriptions ouvertes.
     - `REGISTRATION CLOSED` : Inscriptions fermées.
     - `ONGOING` : Événement en cours.
     - `FINISHED` : Événement terminé.

2. **Sécurité et authentification**
   - Gestion des utilisateurs avec rôles.
   - Accès limité aux fonctionnalités en fonction du rôle (Admin/Utilisateur).

3. **Langues**
   - Interface multilingue : Français par défaut, support de l'anglais.
   - Traductions centralisées via Symfony Translator.

4. **Design et UX**
   - Interface moderne et épurée.
   - Optimisation mobile avec Tailwind CSS.

5. **Administration**
   - Tableau de bord admin pour gérer les utilisateurs et événements.

6. **Asynchronisme**
   - Envoi d'emails et traitement des états d'événements via Symfony Messenger.

---

## 🌟 À venir

- Statistiques sur les participants et événements. ?
- Intégration d'autre(s) langues.
- Notifications en temps réel via WebSockets.
- Créer un groupe privé.
- Chat en temps réel avec WebSockets.

---

## 📧 Support

Pour toute question ou problème, contactez [Lénaïc Barbier](https://gitlab.com/lenaicb22), ou même [Dorian Pesce](https://gitlab.com/Phyrios) et vraiment en dernier recours.. [Florian Heuze](https://gitlab.com/flo2167).

---

**SortirPointCom** est un projet développé avec passion pour simplifier l'organisation et la gestion des événements entre stagiaires. 🎉
