L'analyse de l'architecture technique de LesFurets.com, en particulier la manière dont est géré leur formulaire
de         
comparaison (le cœur de leur service), révèle une approche très structurée mêlant robustesse historique et
modernisation   
continue.

Voici comment est conçu et géré leur système de formulaire de comparaison, d'après les interventions publiques de
leurs    
équipes techniques (conférences, interviews de CTO, et retours d'expérience sur leur écosystème Java/Front) :  
──────

### 1. La logique du formulaire : Un Questionnaire Dynamique et Conditionnel

Les formulaires de comparaison (assurance auto, habitation, énergie, etc.) sont particulièrement complexes car ils  
comportent des dizaines de questions dépendantes les unes des autres.

• Moteur de parcours dynamique (Wizard) : Le formulaire n'est pas figé. Les questions suivantes s'adaptent en temps réel
en
fonction des réponses précédentes (par exemple, si vous répondez que vous n'avez pas de second conducteur, toutes les  
questions relatives à ce dernier sont masquées).  
• Validation en temps réel (Client-side) : Pour réduire le taux d'abandon (drop rate), chaque champ est
validé             
immédiatement en cours de saisie (format de plaque d'immatriculation, cohérence des dates d'obtention de permis, etc.)
sans
avoir à soumettre l'intégralité de la page.

### 2. Transition Technologique du Front-End : De GWT vers React

La gestion technique de l'interface utilisateur a connu une évolution majeure :

• L'ère GWT (Google Web Toolkit) : Historiquement, LesFurets utilisait beaucoup GWT côté front-end. Cette
technologie      
compilait du code Java en JavaScript. Cela permettait à leurs nombreux développeurs Java backend de maintenir une
cohérence
et de concevoir des formulaires directement depuis le modèle de données Java de
l'application.                             
• Modernisation avec React : Pour gagner en performance de rendu, en fluidité d'UX (transitions animées,
micro-            
interactions) et en agilité de développement, le front-end a migré vers React. Les formulaires complexes sont
désormais    
découpés en composants React réutilisables et autonomes (boutons à choix multiples personnalisés, date pickers
optimisés,  
inputs avec auto-complétion).  
• State Management : L'état global du questionnaire (toutes les réponses apportées par l'utilisateur au fil des étapes)
est
stocké localement dans l'application SPA (Single Page Application) et mis à jour de manière asynchrone avant d'être
validé
par le serveur.

### 3. Le Moteur de Règles Métier (Rules Engine) et le Backend Java

Le front-end (React) ne sert que de couche de présentation et de validation de premier niveau. Toute la puissance
réside   
dans le backend :

• Backend 100% Java / Spring Boot : La logique d'orchestration est gérée par des microservices
Java.                       
• Modélisation par objets (Beans) : Les formulaires sont définis côté serveur sous forme de structures de
données          
dynamiques (souvent appelées objets Question ). C'est le serveur qui fournit au front-end les règles de validation et
la  
structure du questionnaire pour le produit demandé (auto, santé,
etc.).                                                    
• Moteur de règles (Rules Engine) : Pour vérifier la cohérence absolue d'un devis et s'assurer que les données
respectent  
les critères des assureurs partenaires, les données soumises passent par un moteur de règles robuste côté
serveur.         
• Intégration d'APIs tierces en temps réel : Une fois le formulaire validé et soumis, le serveur centralise les réponses
au
format JSON et interroge simultanément les serveurs de dizaines de partenaires assureurs via leurs API respectives.
Les    
réponses (les offres de prix et garanties) sont ensuite agrégées et triées pour être affichées sur la page de résultats.

### 4. Suivi et Optimisation du Parcours (Data & Graphes)

Chaque clic et chaque abandon sur le formulaire est une information cruciale pour optimiser la conversion :

• Suivi d'événements (Analytics) : Des outils de tracking mesurent précisément le temps passé sur chaque question
pour     
détecter les frictions
ergonomiques.                                                                                       
• Architecture Graphe (Neo4j) : LesFurets a partagé des retours d'expérience sur l'utilisation de bases de données
de      
graphes (Neo4j) pour modéliser le parcours complet des utilisateurs (les différentes étapes franchies, les
allers-retours  
dans le questionnaire) et identifier les utilisateurs uniques à travers plusieurs appareils (cross-device).

### 5. L'évolution vers le Conversationnel (IA Générative)

Récemment (courant 2026), LesFurets a élargi sa gestion de formulaire en introduisant des interfaces basées sur
l'IA       
générative (notamment via un plugin ChatGPT) :

• Au lieu de remplir le questionnaire traditionnel de 30 à 40 champs, l'utilisateur peut simplement discuter avec un
agent
conversationnel qui lui pose 4 questions clés pour estimer son profil de risque à partir de modèles de données
préexistants
et lui fournir une estimation
rapide.                                                                                      
──────

### En résumé

Le formulaire de comparaison de LesFurets est géré comme un système hybride :

1. Un front-end dynamique moderne sous React pour une expérience utilisateur rapide, fluide et interactive.
2. Un backend robuste en Java / Spring Boot qui héberge le moteur de règles métier, génère la structure dynamique
   du       
   questionnaire et pilote les appels API en temps réel vers les partenaires.
3. Une infrastructure Cloud (Google Cloud Platform - GCP) pour assurer la scalabilité nécessaire au traitement
   instantané  
   de milliers de formulaires en simultané.                                                                                   
