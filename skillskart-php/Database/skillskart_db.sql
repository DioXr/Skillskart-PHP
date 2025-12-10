-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 05:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillskart_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `assigned_language_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `assigned_language_id`) VALUES
(2, 'admin', '$2y$10$TdE8wu9r9P593smCq9mZ3uh79RLdhCwAYa69KpooUoACUPWuw/S1y', NULL),
(3, 'shubham', '$2y$10$GT4CPtpCTTmwYA9UrOtXj.zv9lhD4UbsRmddKRUPW.MQyTn7JqZe6', 'python'),
(4, 'kavya', '$2y$10$DDeRqxuIMtDwzhshTKckPuTdggAVSiQ229Mf03XOmIADFovAeyyXi', 'golang'),
(5, 'Chirag', '$2y$10$tJc/4EAWODnOZzAnVLV6aOWPd9bogIPvTpGEu5f9sxI0SDgJk0xK2', 'react'),
(7, 'Mayank', '$2y$10$9p741s9l0bVMxoLBUxND1utu63/wha.bLYvLLy2Qjc.JUJtr110x6', 'javascript');

-- --------------------------------------------------------

--
-- Table structure for table `content_blocks`
--

CREATE TABLE `content_blocks` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `type` enum('paragraph','code','heading','list') NOT NULL,
  `value` longtext NOT NULL,
  `order_index` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_blocks`
--

INSERT INTO `content_blocks` (`id`, `topic_id`, `type`, `value`, `order_index`) VALUES
(1, 1, 'paragraph', 'In Python, a variable is created the moment you first assign a value to it. This is called dynamic typing.', 1),
(2, 1, 'code', 'x = 5\ny = \"Hello, World!\"', 2),
(3, 2, 'paragraph', 'Operators are used to perform operations on variables and values.', 1),
(6, 6, 'paragraph', 'Functions are blocks of code designed to perform a particular task. A JavaScript function is executed when \'something\' invokes it (calls it).', 1),
(7, 6, 'code', '// Function Declaration\r\nfunction greet(name) {\r\n  return \"Hello, \" + name + \"!\";\r\n}\r\n\r\n// Calling the function\r\nlet message = greet(\"Alice\");\r\nconsole.log(message); // Outputs: Hello, Alice!', 2),
(9, 5, 'paragraph', 'In JavaScript, variables are named containers used to store data values. They are essential for storing, retrieving, and manipulating data within a program. JavaScript offers three primary keywords for declaring variables: var, let, and const. \r\n', 1),
(10, 5, 'code', 'function exampleVar() {\r\n  var x = 10;\r\n  if (true) {\r\n    var x = 20; // Re-declaration and reassignment is allowed\r\n    console.log(x); // Output: 20\r\n  }\r\n  console.log(x); // Output: 20\r\n}\r\nexampleVar();', 2),
(11, 7, 'paragraph', 'JavaScript utilizes arithmetic operators to perform mathematical calculations on numerical values. These operators take operands (numbers or variables holding numbers) and return a single numerical value.\r\n', 1),
(12, 7, 'code', '    let sum = 5 + 3; // sum will be 8', 2),
(14, 9, 'paragraph', 'JSX stands for JavaScript XML. It allows us to write HTML-like syntax inside our JavaScript code.', 1),
(15, 10, 'paragraph', 'Components are the building blocks of React apps. Props are used to pass data from parent to child components.', 1),
(18, 11, 'paragraph', 'Allows you to add state to function components.', 1),
(19, 11, 'code', 'const [count, setCount] = useState(0);', 2),
(20, 12, 'paragraph', 'Perform side effects (like data fetching) in function components.', 1),
(21, 12, 'code', 'useEffect(() => { document.title = count; }, [count]);', 2),
(22, 13, 'paragraph', 'Standard library for routing in React', 1),
(23, 13, 'code', '<BrowserRouter><Routes><Route path=\"/\" element={<Home />} /></Routes></BrowserRouter>', 2),
(24, 14, 'paragraph', 'Finding HTML elements by ID, tag name, class name, and CSS selectors.\r\n\r\n', 1),
(25, 14, 'code', 'document.getElementById(\"demo\");\r\n', 2),
(26, 15, 'paragraph', ' Waiting for user interaction like clicks or key presses.\r\n', 1),
(27, 15, 'code', ' btn.addEventListener(\"click\", displayDate);\r\n', 2),
(28, 17, 'paragraph', 'Syntactic sugar built on top of Promises, making asynchronous code look and behave like synchronous code.\r\n\r\n', 1),
(29, 3, 'paragraph', 'Decision making in Python.', 1),
(30, 3, 'code', 'if x > 10: print(\"Big\")\r\nelse:\r\nprint(\"small\")', 2),
(31, 18, 'paragraph', 'Used for iterating over a sequence (that is either a list, a tuple, a dictionary, a set, or a string).\r\n', 1),
(32, 18, 'code', 'for x in \"banana\": print(x)', 2),
(33, 19, 'paragraph', 'A function is a block of code which only runs when it is called.', 1),
(34, 19, 'code', 'def my_function(): print(\"Hello\")\r\n', 2);

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `description`, `image`) VALUES
('golang', 'Go', 'Language developed by google!', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/go/go-original-wordmark.svg'),
('javascript', 'JavaScript', 'The core language of the web for interactive frontends.', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg'),
('python', 'Python', 'Versatile language for web, data science, and automation.', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg'),
('react', 'React', 'A JavaScript library for building user interfaces.', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `language_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `order_index` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `language_id`, `title`, `order_index`) VALUES
(1, 'python', 'Module 1: The Basics', 1),
(2, 'python', 'Module 2: Control Flow', 2),
(4, 'javascript', 'Module 1: JS Fundamentals', 1),
(6, 'javascript', 'Module 2: Operators', 2),
(8, 'react', 'Module 1: Core Concepts', 1),
(10, 'react', 'Module 2: State & Effects', 2),
(11, 'react', 'Module 3: Routing', 3),
(12, 'javascript', 'Module 3: DOM Manipulation', 3),
(13, 'javascript', 'Module 4: Asynchronous', 4),
(14, 'python', 'Module 3: Functions', 3);

-- --------------------------------------------------------

--
-- Table structure for table `note_requests`
--

CREATE TABLE `note_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `status` enum('pending','fulfilled') NOT NULL DEFAULT 'pending',
  `response_notes` text DEFAULT NULL,
  `response_file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_requests`
--

INSERT INTO `note_requests` (`id`, `user_id`, `topic_id`, `status`, `response_notes`, `response_file_path`, `created_at`) VALUES
(1, 1, 2, 'fulfilled', 'https://www.youtube.com/live/Tk8sjwpffFY?si=uDCY63wdP-nPKcmZ', NULL, '2025-09-30 15:23:00'),
(2, 2, 2, 'pending', NULL, NULL, '2025-09-30 15:41:25'),
(3, 1, 5, 'pending', NULL, NULL, '2025-09-30 16:25:04'),
(4, 2, 1, 'pending', NULL, NULL, '2025-09-30 21:44:33'),
(5, 1, 1, 'pending', NULL, NULL, '2025-10-01 04:51:49'),
(6, 1, 7, 'pending', NULL, NULL, '2025-10-01 04:54:15'),
(7, 1, 3, 'pending', NULL, NULL, '2025-11-04 12:28:24'),
(8, 1, 10, 'pending', NULL, NULL, '2025-12-02 14:34:20'),
(9, 1, 9, 'pending', NULL, NULL, '2025-12-02 15:15:46'),
(10, 9, 2, 'pending', NULL, NULL, '2025-12-02 16:40:45'),
(11, 11, 1, 'pending', NULL, NULL, '2025-12-05 04:21:58'),
(12, 13, 16, 'pending', NULL, NULL, '2025-12-05 07:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `question` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_options`
--

CREATE TABLE `quiz_options` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roadmaps`
--

CREATE TABLE `roadmaps` (
  `id` int(11) NOT NULL,
  `language_id` varchar(50) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roadmaps`
--

INSERT INTO `roadmaps` (`id`, `language_id`, `content`) VALUES
(1, 'python', '{\"modules\":[{\"title\":\"Module 1: The Basics\",\"topics\":[{\"id\":\"variables-and-data-types\",\"title\":\"Variables & Data Types\",\"isPremium\":false,\"content\":[{\"type\":\"paragraph\",\"value\":\"In Python, a variable is created the moment you first assign a value to it.\"}],\"quiz\":[]}]}]}');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `module_id`, `slug`, `title`, `is_premium`) VALUES
(1, 1, 'variables-and-data-types', 'Variables & Data Types', 0),
(2, 1, 'operators', 'Operators', 1),
(3, 2, 'if-else-statements', 'If/Else Statements', 0),
(5, 4, 'js-variables', 'Variables', 0),
(6, 4, 'js-functions', 'Functions', 0),
(7, 6, 'js-arithmetic', 'Arithmetic Operators', 1),
(9, 8, 'react-jsx', 'What is JSX?', 0),
(10, 8, 'react-components', 'Components and Props', 1),
(11, 10, 'r-useState', 'useState Hook', 0),
(12, 10, 'r-useEffect', 'useEffect Hook', 0),
(13, 11, 'r-React Router Setup', 'React Router Setup', 0),
(14, 12, 'js-Topic: Selecting Elements', 'Selecting Elements', 0),
(15, 12, 'js-Event Listeners', 'Event Listeners', 0),
(16, 13, 'js-Promises', 'Promises', 0),
(17, 13, 'js-Async/Await', 'Async/Await', 1),
(18, 2, 'p-For Loops', 'For Loops', 1),
(19, 14, 'p-Defining Functions', 'Defining Functions', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `subscription` varchar(20) NOT NULL DEFAULT 'free'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `subscription`) VALUES
(1, 'shubh23006@gmail.com', '$2y$10$sI.eXkPWjwx63KltcuBaMe7AHx86/rqbIbuSZQ8kU3rNRQXXeXpPG', 'premium'),
(2, 'kavyabirade@gmail.com', '$2y$10$OdZ4pVnPN/A6wFVo1Up7LeM1pvVTamGPO.JHOpta65v/xLNybzD8a', 'premium'),
(3, 'shubh23@gmail.com', '$2y$10$txImTs46GnqWTlmVYMIN1egBVuXWCclrwOQ1KCNicEmpP0Nmhc3CW', 'premium'),
(6, 'test11@gmail.com', '$2y$10$42VxeyA6YMH1rZhEHDt/ZOjReuVtbcvIiYngzi3Gu1fYtSrnIc07S', 'free'),
(9, 'test3@gmail.com', '$2y$10$qMPDgR5cOTPOiLwP0cX5c.d96YEkvboAZEK9oHxWXoNy8a4bmyKyS', 'premium'),
(10, 'mayank@gmail.com', '$2y$10$M69W9AE27refjAXxEi4dieFFIha66BwHeNVmGPJohj3el1kkecx8u', 'premium'),
(11, 'you@gmail.com', '$2y$10$6OSVpv6rccSGfTN2/CCFsOf2d1d3veifObVWKhCEVKvOy00oYlN5W', 'free'),
(12, 'dhiraj@gmail.com', '$2y$10$7ztUagpb1UfFSw7FHKglbuHzI4eagA2klVC.N4rWIvUBQifDKXl8O', 'free'),
(13, 'ishaan@gmail.com', '$2y$10$uguqkr86qGCON/shoHNH7.R.ymaarDvVviJN6HrfRn2pcrvo/bel2', 'premium');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `topic_id`) VALUES
(32, 1, 5),
(30, 1, 7),
(9, 1, 10),
(3, 2, 1),
(35, 2, 3),
(5, 3, 6),
(4, 3, 9),
(43, 9, 2),
(44, 10, 1),
(45, 10, 2),
(46, 10, 3),
(47, 10, 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `assigned_language_id` (`assigned_language_id`);

--
-- Indexes for table `content_blocks`
--
ALTER TABLE `content_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `note_requests`
--
ALTER TABLE `note_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `roadmaps`
--
ALTER TABLE `roadmaps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `language_id` (`language_id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_topic_unique` (`user_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `content_blocks`
--
ALTER TABLE `content_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `note_requests`
--
ALTER TABLE `note_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_options`
--
ALTER TABLE `quiz_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roadmaps`
--
ALTER TABLE `roadmaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`assigned_language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `content_blocks`
--
ALTER TABLE `content_blocks`
  ADD CONSTRAINT `content_blocks_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_requests`
--
ALTER TABLE `note_requests`
  ADD CONSTRAINT `note_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_requests_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD CONSTRAINT `quiz_options_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roadmaps`
--
ALTER TABLE `roadmaps`
  ADD CONSTRAINT `roadmaps_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
