# Ecosystème

La vue de l’écosystème décrit l’ensemble des entités ou systèmes qui gravitent autour du système d’information considéré dans le cadre de la cartographie.

Cette vue permet à la fois de délimiter le périmètre de la cartographie, mais aussi de disposer d’une vision d’ensemble de l’écosystème sans se limiter à l’étude individuelle de chaque entité.

Les entités et relations permettent de répondre à l’objectif de sécurité 3 (maîtrise de l’écosystème) du référentiel RECYF (référentiel national français NIS2). Les points de cartographie de l’écosystème (3.A.1 et 3.A.2) sont couverts sauf en ce qui concerne les interconnexions avec les SI de [entité], traités dans l’objet « Entités extérieures connectées »

## Entités

Les entités sont une partie de l’organisme (ex. : filiale, département, etc.) ou en relation avec le système d’information qui vise à être cartographié.

### Actifs représentés et justifications

Dans le périmètre de la cartographie des SI de [entité], les entités peuvent représenter :

* L’entreprise, ses sites, ses services (centraux / sites), ses filiales et succursales,
* Les fournisseurs et prestataires des SI de [entité], de manière récursive. Cela permet d’établir les chaînes de dépendances de l’écosystème.
* Les autres tiers utiles.
* Si nécessaire, les clients de l’entreprise.

Les entités représentent également les notions de :

* Compte cloud,
* Région cloud.

Les régions et comptes cloud sont au même niveau, rattachés à une entité parente, le fournisseur cloud. Il est possible de représenter les zones de disponibilités (AZ, *availabilty zones*) en tant qu’entité filles des régions cloud. Pour ne pas complexifier la représentation des SI dans un premier temps, les AZ ne sont pas représentées.

Ce choix a été fait afin de pouvoir lier les objets cloud :

* Stockage,
* Calcul,
* Base de données,

représentés respectivement par les objets :

* « Module applicatif »,
* « Application »,
* « Base de données »,

à un unique type d’objet indiquant la région et le compte cloud.

Une autre représentation est possible, en utilisant les objets :

* « Sites » (fournisseur cloud),
* « Bâtiments / Salles » (compte cloud),
* « Baies » (région cloud),
* « Serveurs physiques » / « Serveurs logiques » (calcul),
* « Infrastructures de stockage » (stockage),
* « Base de données » (base de données).

Cette possibilité est cependant moins satisfaisante car :

* Les compte et régions cloud ne sont pas au même niveau hiérarchique obligeant à la multiplication des instances des régions cloud afin de couvrir l’ensemble des comptes cloud.
* La notion de serveur physique n’existe pas du point de vue du client d’un fournisseur cloud.
* Les objets « infrastructures de stockage » ne peuvent se rattacher qu’à des objets « serveurs logiques », en tant qu’infrastructures de sauvegarde, sauf à utiliser des objets « liens physiques » ou « flux logiques », ce qui complexifie la représentation et sa maintenabilité.
* Un site ne peut être rattaché à une entité, obligeant à maintenir deux objets différents et sans lien afin de représenter les objets des SI d’un côté et les relations de fourniture de biens et services de l’autre.

### Référence des champs de l’interface graphique

| Champs interface graphique | Données proposées | Contraintes de saisie |
| :--- | :---: | :--- |
| Nom | Saisie libre | Unicité parmi les entités, entre 3 et 64 caractères |
| Type d’entité | Saisie libre + liste déroulante des types d’entités | 0 à 1 type d’entité, 255 caractères par type au plus |
| Entité parente | Liste déroulante des entités | 0 à 1 seule entité parente |
| Externe à l’organisme | S/O | Bouton à commuter |
| Description | S/O | Voir X |
| Sélectionnez une icône | Icônes d’entités déjà renseignées | Voir X |
| Point de contact | S/O | Voir X |
| Niveau de sécurité | S/O | Voir X |
| Applications | Liste déroulante des applications | 0 à n choix |
| Bases de données | Liste déroulante des bases de données | 0 à n choix |
| Processus soutenus | Liste déroulante des processus | 0 à n choix |

### Champs obligatoires pour la création

Le champ obligatoire pour créer une entité est :

* Son nom

Si le nom dépasse 64 caractères, dans le cas d’un copier / coller par exemple, celui-ci est automatiquement tronqué aux 64 premiers caractères.

Le nom d’une entité doit être issu de [source de vérité] (clients, fournisseurs, etc.) ou de [source de vérité] (structure de l’entreprise).

### Utilisation / Choix fonctionnels des autres champs

Le champ « Type d’entité » sert à indiquer ce qu’est une entité (service central, site, succursale, prestataire, fournisseur, etc.). La liste complète se trouve dans le fichier X (masterdata).

Le champ « Description » ne contient pas d’informations normées et est à la main du rédacteur.

Le champ « Entité parente » décrit l’appartenance hiérarchique d’une entité, ou éventuellement un lien capitalistique. Ce champ est essentiellement dédié à la description de l’organisation de [entité].

Les autres cas doivent être gérés à travers les objets « Relations ».

Le bouton « Externe à l’organisme » doit être inactif (à gauche) pour l’ensemble des entités dépendantes de [entité], y compris les filiales. Les autres entités, y compris la ou les holdings sont considérées comme externe (bouton à droite).

Le champ « Point de contact » permet de gérer les contacts commerciaux et techniques pour les fournisseurs, prestataires et clients. Ce champ peut être issu d'une source de vérité externe [source de vérité] ou être la source de vérité pour [entité].

La gestion du niveau de sécurité attendu d’une entité (champ « Niveau de sécurité ») [compléter]

Les champs « Applications » et « Bases de données » indiquent les applications (resp. les bases de données) dont l’entité est responsable de l’exploitation.

Le champ « Processus soutenus » indique si l’entité prend part à un processus de l’entreprise.

### Comportements par défaut

Par défaut, une entité est créée comme interne à l’organisme. Le bouton « Externe à l’organisme » doit être commuté si nécessaire.

### Source de données

Résultat de la transaction [source de vérité] décrivant la structure de l’entreprise.
Résultat de la transaction [source de vérité] et des bornes temporelles.
