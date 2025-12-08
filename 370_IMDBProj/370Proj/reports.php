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
     | movie_title             |year_released|
     |_________________________|_____________|
     | The Dark Knight         | 2006        |
     | Mad Max: Fury Road      | 2006        |
     |_______________________________________|
     Comedy
     _________________________________________
     | movie_title             |year_released|
     |_________________________|_____________|
     | The Grand Budapest Hotel| 2006        |
     | Superbad                | 2006        |
     |_______________________________________|
*/
SELECT g.genre_description, m.movie_title, m.year_released
FROM Movies m                                  
JOIN genre g ON m.genre_code = g.genre_code
ORDER BY g.genre_description ASC, m.year_released ASC;


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
SELECT
    m.movie_title,
    CONCAT(a.fname, ' ', a.lname) AS actor_name,
    a.DOB
FROM Movies m
JOIN Casting c
    ON m.movie_id = c.movie_id
JOIN Actor a
    ON c.actor_id = a.actor_id
ORDER BY
    m.movie_title,
    a.lname,
    a.fname;
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
