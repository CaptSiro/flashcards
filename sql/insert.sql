INSERT INTO `users` (`id`, `email`) VALUES
(2, 'captsiro@gmail.com');

INSERT INTO `decks` (`id`, `name`, `users_id`) VALUES
(1, 'Hardware', 2),
(2, 'WA', 2),
(3, 'Četba', 2),
(4, 'AJ', 2);

INSERT INTO `stacks` (`id`, `name`, `decks_id`) VALUES
(1, 'Displeje', 1),
(2, 'Pameti', 1);

INSERT INTO `cards` (`id`, `question`, `answer`, `decks_id`) VALUES
(3, 'asdf', 'asdf', 1),
(4, 'fdsa', 'fdsa', 1);

INSERT INTO `cards_in_stacks` (`cards_id`, `stacks_id`) VALUES
(3, 1),
(4, 1);