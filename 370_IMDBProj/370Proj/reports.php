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
     | actor_name               | DOB               |
     |__________________________|___________________|
     | Leonardo DiCaprio        | 1988-11-06        |
     | Joseph Gordon-Levitt     | 1988-11-06        |
     | Ellen Page               | 1988-11-06        |
     |__________________________|___________________|

     The Matrix
     ________________________________________________
     | actor_name               | DOB               |
     |__________________________|___________________|
     | Keanu Reeves             | 1988-11-06        |
     | Laurence Fishburne       | 1988-11-06        |
     | Carrie-Anne Moss         | 1988-11-06        |
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
SELECT
    l.language_description AS language,
    m.movie_title,
    m.year_released
FROM Movies m
JOIN Language l
    ON m.original_language_code = l.original_language_code
ORDER BY l.language_description, m.year_released, m.movie_title;
