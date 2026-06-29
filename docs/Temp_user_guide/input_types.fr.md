# Types de saisies

Différentes manières de saisir des données sont présentes dans l’interface graphique de Mercator.

## Généralités

Les diacritiques (accentuations, cédille, etc.) et les langues non-occidentales sont prises en charge par défaut (techniquement : UTF8mb4).

## Saisie libre

La saisie libre est la saisie d’informations sans proposition automatique de remplissage en puisant dans le champ courant parmi d’autres instances de l’objet. En général, il s’agit des noms des objets ou des descriptions.

Il y a des limites de nombre de caractères pour les champs en saisie libre, le plus souvent de 255 ou de 4,3 milliards (2<sup>32</sup> - 1) de caractères.

## Saisie libre de date

La saisie libre de date est un type de saisie où l’information requise dans un champ est une date. Celle-ci peut être librement choisie dans un calendrier ou via les flèches directionnelles du clavier.

Le calendrier permet de :

* sélectionner la date du mois courant (grille des jours),
* sélectionner la date du jour (bouton),
* d’effacer la date déjà saisie (bouton),
* de naviguer de mois en mois (boutons),
* de naviguer d’année en année (bouton).

## Saisie libre avec liste déroulante

La saisie libre avec liste déroulante permet de saisir des informations communes à plusieurs instances d’un objet, comme des types, des attributs, des responsables, etc.

Ce type de saisie se présente comme un champ de texte libre ou une liste déroulante. Au clic :

* la saisie devient possible
* une liste des valeurs du champ pour les autres instances de l’objet s’affiche, par ordre alphabétique,
* le texte entré recherche (plein texte) parmi la liste existante et permet de d’ajouter une nouvelle valeur si besoin.

La sélection courante est le texte en fond bleu. La navigation est possible avec les flèches directionnelles du clavier. La touche « entrée » permet de valider la sélection.

## Liste déroulante

Une saisie sous forme de liste déroulante permet de choisir parmi les instances existantes d’un type d’objet (liste des entités, des serveurs physiques par ex.). Cela sert à lier les objets de Mercator les uns aux autres.

Une saisie sous forme de liste déroulante est fermée. Il n’est pas possible de créer une instance d’objet à lier par ce biais. Il faut d’abord créer l’instance du type d’objet à lier pour qu’il soit accessible via ce champ.

Une fonction de recherche est également disponible. Le texte entré cherche (plein texte) parmi la liste.

La liste est classée par ordre alphabétique

La sélection courante est le texte en fond bleu. La navigation est possible avec les flèches directionnelles du clavier. La touche « entrée » permet de valider la sélection.
