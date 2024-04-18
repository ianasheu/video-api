# VIDEO-API

## API REST EN PHP POUR BASE DE DONNEES SUR DES FILMS

Le premier travail procède en un découplage d'avec la base de données. Pour cela, plutôt que d'utiliser un framework, j'ai envie de développer l'api rest moi-même en php afin de couvrir les problématiques.


## SCHEMA RELATIONNEL DE LA BASE DE DONNEES

![](video-api-bdd-schema.png "")
> _video-api-bdd-schema.png_


## REQUETE SQL DE CREATION DES TABLES DE LA BASE DE DONNEES

```
CREATE TABLE `movie` (
	`id` int NOT NULL auto_increment,
	`title` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	`year` int DEFAULT NULL,
	`rating` float DEFAULT NULL,
	`poster` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
	`allocine` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
	CONSTRAINT `movie_pk` PRIMARY KEY (`id`),
	CONSTRAINT `movie_u` UNIQUE (`id`),
	INDEX (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

```
CREATE TABLE `director` (
	`id` int NOT NULL auto_increment,
	`name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	`country` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
	CONSTRAINT `director_pk` PRIMARY KEY (`id`),
	CONSTRAINT `director_u` UNIQUE (`id`),
	INDEX (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```
> (*) Dans la table des réalisateurs, le champ _country_, récent, ne contient pas encore de données renseignées dans la base.

```
CREATE TABLE `moviedirector` (
	`id` int NOT NULL auto_increment,
	`movie` int NOT NULL,
	`director` int NOT NULL,
	CONSTRAINT `moviedirector_pk` PRIMARY KEY (`id`),
	CONSTRAINT `moviedirector_u` UNIQUE (`id`),
	CONSTRAINT `movied_fk` FOREIGN KEY (`movie`) REFERENCES `movie` (`id`),
	CONSTRAINT `director_fk` FOREIGN KEY (`director`) REFERENCES `director` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

```
CREATE TABLE `category` (
	`id` int NOT NULL auto_increment,
	`tag` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	CONSTRAINT `category_pk` PRIMARY KEY (`id`),
	CONSTRAINT `category_u` UNIQUE (`id`),
	INDEX (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

```
CREATE TABLE `moviecategory` (
	`id` int NOT NULL auto_increment,
	`movie` int NOT NULL,
	`category` int NOT NULL,
	CONSTRAINT `moviecategory_pk` PRIMARY KEY (`id`),
	CONSTRAINT `moviecategory_u` UNIQUE (`id`),
	CONSTRAINT `moviec_fk` FOREIGN KEY (`movie`) REFERENCES `movie` (`id`),
	CONSTRAINT `category_fk` FOREIGN KEY (`category`) REFERENCES `category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```
Afin de limiter les messages d'erreur, la base de données est configurée pour tronquer les données si elles dépassent la taille du champ cible.


## DIAGRAMME DE CLASSES UML DU CODE PHP DE L'API

![](video-api-uml.drawio.svg "")
> _video-api-uml.drawio.svg_

Commentaires : Le type des propriétés de classe n'est pas renseigné s'il est scalaire (booléen, entier, nombre décimal, chaîne de caractères). En revanche, il est renseigné si c'est un tableau ou un objet. Il en sera de même pour les types des arguments des méthodes de classe ainsi que les types de valeur de retour. De plus, le type de retour d'une méthode qui ne retourne rien sera indique avec void (sauf pour les constructeurs). Ce n'est donc pas du mode strict.

Limitations : Il n'y a pas de version ni de cache. Sont abscents de l'archive les fichiers Controllers/users_config.php et Models/db_config_ovh.php. De plus, sont aussi abscents du diagramme les fichiers .htaccess, index.php, Autoloader.php, Models/MovieItemModel.php, Models/DirectorItemModel.php, Models/CategoryItemModel.php et Views/json_view.php.


## DESCRIPTION DES APPELS

#### RACINE DE L API
> /video/api

#### RESSOUCES
> - movie
> - director
> - category

Un content au format json est nécessaire pour les methods POST et PUT. Une authentification basique est demandée pour les methods autres que GET. La valeur renseignée pour l'id, que ce soit dans l'url ou dans le content, doit être numérique.

#### FILTRES
> - orderby
> - limit
> - offset
> - detailed

Utilisable pour les methods GET. Le critère passé à orderby doit être une propriété de la ressource avec laquelle il est utilisé. Les valeurs passées à limit et offset doivent être numériques. Les valeurs passées à detailed doivent être true ou false.

## MOVIE

Les valeurs pour year et rating doivent être numériques.

```
	method POST
	request
		/video/api/movie
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"title" : "createdMovie",
				["year" : "releaseYear",]
				["rating" : "fiveRating",]
				["poster" : "fileNameJpg",]
				["allocine" : "idAllocine"]
			}
	response
		201 : newId
```

```
	method POST
	request
		/video/api/movie/director
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"movie" : "associateMovie",
				"director" : "associateDirector"
			}
	response
		201 : newId
```

```
	method POST
	request
		/video/api/movie/category
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"movie" : "associateMovie",
				"director" : "associateDirector"
			}
	response
		201 : newId
```

```
	method GET
	request
		/video/api/movie
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/movie?orderby=year&limit=37&offset=8](api/movie?orderby=year&limit=37&offset=8)

```
	method GET
	request
		/video/api/movie/id/{id}
		filtres possibles : detailed
	response
		200 : []
```
> exemple : [/movie/id/1127?detailed=true](api/movie/id/1127?detailed=true)

```
	method GET
	request
		/video/api/movie/id/{id}/director
	response
		200 : []
```
> exemple : [/movie/id/1127/director](api/movie/id/1127/director)

```
	method GET
	request
		/video/api/movie/id/{id}/category
	response
		200 : []
```
> exemple : [/movie/id/1127/category](api/movie/id/1127/category)

```
	method GET
	request
		/video/api/movie/title/{*title*}
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/movie/title/\*glace\*age\*](api/movie/title/*glace*age*)

```
	method GET
	request
		/video/api/movie/year/{year}
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/movie/year/2020](api/movie/year/2020)

```
	method GET
	request
		/video/api/movie/rating/{rating}
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/movie/rating/3.5?orderby=year&limit=10&offset=90](api/movie/rating/3.5?orderby=year&limit=10&offset=90)

```
	method PUT
	request
		/video/api/movie
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"id" : "id",
				"title" : "updatedTitle",
				["year" : "releaseYear",]
				["rating" : "fiveRating",]
				["poster" : "fileNameJpg",]
				["allocine" : "idAllocine"]
			}
	response
		202 : countRow
```

```
	method DELETE
	request
		/video/api/movie/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```

```
	method DELETE
	request
		/video/api/movie/id/{id}/director/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```

```
	method DELETE
	request
		/video/api/movie/id/{id}/category/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```

## DIRECTOR

```
	method POST
	request
		/video/api/director
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"name" : "createdDirector",
				["country" = "countryCode"]
			}
	response
		201 : newId
```

```
	method POST
	request
		/video/api/director/movie
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"movie" : "associateMovie",
				"director" : "associateDirector"
			}
	response
		201 : newId
```

```
	method GET
	request
		/video/api/director
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/director?orderby=name&limit=12&offset=0](api/director?orderby=name&limit=12&offset=0)

```
	method GET
	request
		/video/api/director/id/{id}
		filtres possibles : detailed
	response
		200 : []
```
> exemple : [/director/id/46](api/director/id/46)

```
	method GET
	request
		/video/api/director/id/{id}/movie
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/director/id/46/movie](api/director/id/46/movie)

```
	method GET
	request
		/video/api/director/name/{*name*}
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/director/name/\*sp\*](api/director/name/*sp*)

```
	method GET
	request
		/video/api/director/country/{country}
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/director/country/US](api/director/country/US)

```
	method PUT
	request
		/video/api/director
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"id" : "id",
				"name" : "updatedName",
				["country" : "countryCode"]
			}
	response
		202 : countRow
```

```
	method DELETE
	request
		/video/api/director/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```

```
	method DELETE
	request
		/video/api/director/id/{id}/movie/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```

## CATEGORY

```
	method POST
	request
		/video/api/category
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"tag" : "createdCategory"
			}
	response
		201 : newId
```

```
	method POST
	request
		/video/api/category/movie
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"movie" : "associatedMovie",
				"category" : "associatedCategory"
			}
	response
		201 : newId
```

```
	method GET
	request
		/video/api/category
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/category?orderby=tag&limit=6&offset=0](api/category?orderby=tag&limit=6&offset=0)

```
	method GET
	request
		/video/api/category/id/{id}
		filtres possibles : detailed
	response
		200 : []
```
> exemple : [/category/id/5](api/category/id/5)

```
	method GET
	request
		/video/api/category/id/{id}/movie
		filtres possibles : orderby, limit, offset
	response
		200 : []
```
> exemple : [/category/id/5/movie?orderby=year&limit=20&offset=230](api/category/id/5/movie?orderby=year&limit=20&offset=230)

```
	method PUT
	request
		/video/api/category
			Authorization : Basic login:password
			Content-Type : application/json
			Content : {
				"id" : "id",
				"tag" : "updatedTag"
			}
	response
		202 : countRow
```

```
	method DELETE
	request
		/video/api/category/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```

```
	method DELETE
	request
		/video/api/category/id/{id}/movie/id/{id}
			Authorization : Basic login:password
	response
		200 : countRow
```


## CODES HTTP REPONSES POSSIBLES
- 200 Ok
- 201 Created
- 202 Accepted
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 405 Method Not Allowed
- 503 Service Unavailable
