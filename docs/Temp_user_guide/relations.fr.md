## Relations

### Actifs représentés et justification

Les objets « Relations » servent à décrire les liens entre deux entités.
Ce peut être des commandes, des contrats, des accords de services, des obligations légales, etc. ayant une influence sur le système d’information.
Les volumes de licences logicielles sont également décrits dans les objets « Relations ».

Le but de la cartographie des relations est de représenter l’ensemble des biens et services achetés par la DSI pour servir l’entreprise et les utilisateurs afin de :

* Disposer d’un outil de communication à destination de l’entreprise
* Disposer d’un outil d’aide à la décision pour la DSI concernant les liens de l’entreprise avec ses prestataires et fournisseurs (périmètre, dépendances, gestion temporelle des contrats / relations), en coordination avec la direction des achats.
* Disposer d’un outil de gestion minimal des chaînes de dépendances des tiers de la DSI

Dans les trois cas, l’un des points importants est de disposer d’un moteur de rendu et non de créer les liens graphiques à la main.

Les relations sont pour l’instant caractérisées comme les commandes passées auprès des fournisseurs et prestataires des SI, regroupées par poste / demande d’achat (DA) / commande similaire.

Une extension de la caractérisation des relations en utilisant une base des contrats juridiques, si existante, peut être intéressante.

Plusieurs relations peuvent co-exister entre une source et une destination. Une source peut fournir plusieurs biens ou services différents à une destination, y compris dans une même relation légale ou commerciale.

Cette décomposition est souhaitable afin d’aider à la prise de décision concernant des périmètres différents.

### Référence des champs de l’interface graphique

| Champs interface graphique | Données proposées | Contraintes de saisie |
| :--- | :---: | :--- |
| Nom | Saisie libre | Unicité parmi les relations, 32 caractères au plus |
| Nature | Saisie libre + liste déroulante | 0 à 1 nature de relation, 255 caractères par type au plus |
| Attributs | Saisie libre + liste déroulante | 0 à n attributs, jusqu’à 255 caractères tout compris (espaces de séparation inclus, hors attribut du bouton « Actif »). L’espace indique la fin d’un attribut et la saisie d’un nouveau. |
| Référence | Saisie libre | 255 caractères au plus |
| Numéro de commande | Saisie libre | 255 caractères au plus |
| Responsable | Saisie libre + liste déroulante | 0 à n, jusqu’à 255 caractères tout compris (virgules et espaces de séparation inclus). Le retour à la ligne (touche « Entrée ») marque la fin de la saisie d’un responsable et le début de saisie d’un nouveau. |
| Source | Liste déroulante des entités | Entité déjà créée |
| Destination | Liste déroulante des entités | Entité déjà créée |
| Description | Saisie libre | Voir X |
| Début | Saisie libre de date | - |
| Fin | Saisie libre de date | - |
| Actif | S/O | Bouton à commuter |
| Importance | Liste déroulante | - |
| Date | Saisie libre de date | - |
| Valeur | Saisie libre | Au plus, 10 chiffres avant la virgule, 2 chiffres après. Supporte les nombres négatifs. |
| Commentaires | Saisie libre | Voir X |
| Documents | S/O | Voir X |

### Champs obligatoires pour la création

Les champs obligatoires pour créer une relation sont :

* Son nom
* Sa source
* Sa destination

La création d’une relation implique de créer au préalable les entités associées.

Si le nom dépasse 32 caractères (copier / coller par exemple), la création sera refusée avec un message d’erreur.

Les noms des relations doivent être fonctionnels et non technique et décrire le bien ou le service acheté afin de rendre compte de son utilisation dans l’entreprise. Ce choix découle de la volonté de disposer d’un outil de communication avec le reste de l’entreprise.

Les noms de relations sont ceux affichés le long des liens de la vue écosystème ainsi que dans les autres outils de visualisation (cartes, exploration, dépendances, requêtes).

La source et la destination d’une relation peuvent être la même entité.

### Utilisation / choix fonctionnels des autres champs

Le champ « Nature » indique la nature de la relation (fourniture de biens & services, partenariat commercial, échanges de biens, etc.).

Ce champ pourrait être calqué sur l’organisation des achats (achats Biens & services, achats matériels, achats assistance technique par ex.) dans l’optique d’une extension hors de la DSI de [entité].

La liste complète des valeurs de ce champ se trouve dans le fichier X (masterdata).

Le champ « Attributs » permet de gérer des métadonnées pour les objets « Relations ». Celles-ci sont gérées à travers le fichier X (masterdata).

Le champ « Référence » permet de gérer le numéro de contrat tel qu’indiqué dans [source de vérité] lorsque la relation est sous forme d’un contrat. Lorsqu’il s’agit d’une commande sans contrat, ou d’un autre cas, ce champ n’est pas renseigné.

Le champ « Numéro de commande » est destiné à accueillir les numéros de commande tels qu’indiqué dans [source de vérité]. Les valeurs doivent être les commandes de l’année en cours et de l’année précédente et être séparées par des virgules.

Le champ « Responsable » indique le responsable de la relation à la DSI. Il est l’interlocuteur privilégié des achats sur celle-ci.

Le champ « Description » doit indiquer :

* L’utilité et le périmètre de la relation,
* Les volumes de licences logicielles achetés,
* L’emplacement des documents administratifs de celle-ci (lien vers la GED DSI, la base contrats juridiques, etc.).

Les indications complémentaires sont à la main du rédacteur.

Le champ « Début » doit indiquer la date de début d’un contrat, de la fourniture d’un service, d’un abonnement, etc.

Le champ « Fin » doit indiquer la date de fin d’un contrat, de la fourniture d’un service, d’un abonnement, etc.

Le champ « Actif » sert à indiquer si la relation est en cours ou à maintenir. Le passage du bouton en inactif (« old ») doit indiquer une relation terminée, pour mémoire et historisation de la vie du SI.

Le champ « Importance » sert à indiquer si la relation est critique ou pas pour la DSI, en fonction des impacts.

Pour dégrossir, la matrice suivante peut servir de guide.

| Importance | Impact [entité] | Impact exploitation | Impact projet | Impact compétences |
| --- | --- | --- | --- | --- |
| Faible | Gêne une activité secondaire d’un service | Pas d’impact sur les machines de production | Relation interchangeable, peu d’impact projet | Compétences DSI [entité] suffisantes pour l’exploitation et les projets. Backup interne. |
| Moyen | Gêne l’activité principale d’un service | Gêne d’utilisation d’une application, perte de redondance, etc. | Interrompt ou retarde les projets mineurs du SI. | Compétences DSI [entité] suffisantes pour l’exploitation et les projets / évolutions mineures. Backup interne |
| Fort | Interrompt l’activité d’un service | Interruption d’une application en production avec activité quotidienne importante | Interrompt ou retarde les projets majeurs du SI (ex. migration applicative). | Compétences DSI [entité] suffisantes pour l’exploitation mais pas pour les projets. Pas de backup interne |
| Critique | Empêche un site de fonctionner | Empêche l’exploitation d’une ou plusieurs application ou d’une partie de l’infrastructure du SI | Interrompt ou retarde les projets structurants du SI. | Compétences non possédées par la DSI [entité] |

Le couple de champ « Date » / « Valeur » indique les valeurs payées au cours d’un contrat ou d’une commande.

Le champ « Commentaires » permet d’indiquer d’éventuels commentaires sur la relation. Ce champ peut être utile pour laisser des instructions succinctes lors de congés ou de changements de postes.

Le champ « Documents » permet d’inclure les documents principaux d’une relation. Dans le contexte de [entité], ce champ ne doit pas être utilisé car d’autres logiciels remplissent ce rôle ([source de vérité 1], [source de vérité 2]).

### Comportements par défaut

Par défaut une relation est créée comme ancienne (old, en fond gris dans la liste des objets). Le bouton « Actif » doit être commuté dans la section « Termes du contrat ».

L’activité d’une relation est gérée comme un attribut (« Actif » / « old ») de l’objet par Mercator.

Le dépassement de la date de fin d’une relation fait passer celle-ci en fond rouge à partir du jour de la date de fin. La date de fin n’est pas liée au bouton « Actif » / « old ». Le bouton ne sera pas automatiquement commuté de « Actif » à « old » lors de l’expiration d’une relation.

Dans la section « Termes du contrat », un couple « Date » / « Valeur » est ajouté en fin de liste. Les valeurs saisies les plus anciennes sont en haut, même si leur date est postérieure à celle d’une valeur saisie plus récemment.

Le couple « Date » / « Valeur » reste en lecture / écriture après la saisie initiale. Il est donc possible de modifier a posteriori ces champs.

Les valeurs du champ « Valeur » sont automatiquement arrondies à deux décimales lors de l’enregistrement de la relation.

### Sources de données

Résultat de la transaction [source de vérité] et des bornes temporelles retraités afin d’obtenir une cohérence fonctionnelle.

[source de vérité]
