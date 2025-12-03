/*
CS 370 - Project 1
Presented 12/8
IMDB Like Database
Katie McLaughlin, Lakiya, Gelo, Aaron, Dalston, Khoi 
*/
/* 
----------------------------------  Katie  ---------------------------------- */
/* 
     Action
     _________________________________________
     | movie_title             | avg_rating  |
     |_________________________|_____________|
     | The Dark Knight         | 9.1         |
     | Mad Max: Fury Road      | 8.5         |
     |_______________________________________|
     Comedy
     _________________________________________
     | movie_title             | avg_rating  |
     |_________________________|_____________|
     | The Grand Budapest Hotel| 8.1         |
     | Superbad                | 7.6         |
     |_______________________________________|
*/
SELECT genre, movie title, avg rating, year
FROM movies table                                   **need to combine(join) movies and genre tables
ORDER BY genre, avg rating


/* ---------------------------------- Lakiya ---------------------------------- */
/*
     Inception
     ________________________________________________
     | actor_name               | role              |
     |__________________________|___________________|
     | Leonardo DiCaprio        | Cobb              |
     | Joseph Gordon-Levitt     | Arthur            |
     | Ellen Page               | Ariadne           |
     |__________________________|___________________|

     The Matrix
     ________________________________________________
     | actor_name               | role              |
     |__________________________|___________________|
     | Keanu Reeves             | Neo               |
     | Laurence Fishburne       | Morpheus          |
     | Carrie-Anne Moss         | Trinity           |
     |__________________________|___________________|
*/
/* ---------------------------------- Angelo ---------------------------------- */
/*
     English
     ___________________________________________
     | movie_title             | year_released |
     |_________________________|_______________|
     | Inception               | 2010          |
     | The Dark Knight         | 2008          |
     | Interstellar            | 2014          |
     |_________________________|_______________|

     Spanish
     ___________________________________________
     | movie_title             | year_released |
     |_________________________|_______________|
     | Pan's Labyrinth         | 2006          |
     | Roma                    | 2018          |
     | The Orphanage           | 2007          |
     |_________________________|_______________|
*/
