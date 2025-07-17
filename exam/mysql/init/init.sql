USE exam;

CREATE TABLE fill_in_the_blanks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    difficulty VARCHAR(50),
    question TEXT,
    answer TEXT,
    INDEX(difficulty)
);

CREATE TABLE multiple_choice (
    id INT PRIMARY KEY AUTO_INCREMENT,
    difficulty VARCHAR(50),
    question TEXT,
    answer VARCHAR(255),
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    option_e VARCHAR(255),
    option_f VARCHAR(255),
    INDEX(difficulty)
);

CREATE TABLE single_choice (
    id INT PRIMARY KEY AUTO_INCREMENT,
    difficulty VARCHAR(50),
    question TEXT,
    answer VARCHAR(50),
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    option_e VARCHAR(255),
    option_f VARCHAR(255),
    INDEX(difficulty)
);

CREATE TABLE true_false (
    id INT PRIMARY KEY AUTO_INCREMENT,
    difficulty VARCHAR(50),
    question TEXT,
    answer VARCHAR(50),
    INDEX(difficulty)
);
