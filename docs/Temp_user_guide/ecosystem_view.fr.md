## Vue Ecosystème

La vue « Ecosystème » est le rendu graphique des objets « Entités » et « Relations ».

Les liens de parenté entre entités sont générés (champ « Entité parente »).

Les relations, qui relient par essence deux entités, sont également affichées avec leur nom comme intitulé à côté de la flèche. Les relations formant une boucle sur la même entité sont également affichées.

Les relations inactives (old) sont affichées sans différenciation visuelle. L’activité d’une relation n’est pas reprise non plus dans la liste des objets sous la vue de l’écosystème.

L’affichage du nom permet de faire la différence entre un lien de parenté et une relation.

L’image renseignée pour une entité s’affiche à la place de l’image par défaut.

Les entités sans liens de parenté et n’étant ni source, ni destination d’une relation sont affichées au plus haut niveau du graphique, les unes à côté des autres.

L’ordre d’affichage de ces entités est défini par ancienneté de création (id – clé primaire – en base de données). Les entités les plus récentes sont les plus à droite.

Dans le cas de graphes disjoints / déconnectés, les graphes non connectés sont étendus à partir de l’entité parente ou de la source de la relation.

### Filtres

Deux filtres sont disponibles dans la vue de l’écosystème :

* Situation (interne ou externe à l’organisme)
* Types (type d’entité)

Le filtre « Situation » a les valeurs suivantes :

* Toutes
* Internes
* Externes

Le filtre « Type » reprend l’ensemble des valeurs renseignées dans le champ « Type d’entité » d’un objet « Entité ».

Les entités n’ayant aucun type apparaitront dans la vue lorsque le filtre Type aura la valeur « Tous les types ».

Pour obtenir la liste (ou le graphe) des entités n’ayant aucun type, il est possible d’utiliser une requête ou le tri de la colonne « Type d’entité » de la vue tableau des objets « Entités ».

### Comportements des filtres


| Situation / Types | Tous les types | Un type |
| :--- | :--- | :--- |
| Toutes | Affiche l’ensemble des parentés et relations entre les entités. | Affiche l’ensemble des parentés et relations entre les entités (internes et/ou externes) de type identique. |
| Internes |  <ul><li>Affiche les parentés entre entités internes.</li>  <li>Affiche les relations entre entités internes.</li>  <li>N’affiche pas les parentés ou relations entre entités externes ni entre une entité interne et une entité externe</li></ul> | <ul><li>Affiche les parentés entre entités internes de type identique.</li>  <li>Affiche les relations entre entités internes de type identiques.</li>  <li>N’affiche pas les parentés ou relations entre entités externes, entre une entité interne ou une entité externe.</li>  <li>N’affiche pas les parentés ou relations entre entités internes de type différents.</li></ul> |
| Externes | <ul><li>Affiche les parentés entre entités externes.</li>  <li>Affiche les relations entre entités externes.</li>  <li>N’affiche pas les parentés ou relations entre entités internes ou entre une entité interne et une entité externe.</li></ul> | <ul><li>Affiche les parentés entre entités externes de type identique.</li>  <li>Affiche les relations entre entités externes de type identiques.</li>  <li>N’affiche pas les parentés ou relations entre entités internes, entre une entité interne ou une entité externe.</li>  <li>N’affiche pas les parentés ou relations entre entités externes de type différents.</li></ul> |
