INSERT INTO users (email, password, full_name) VALUES ('test@example.com', '$2y$10$...', 'John Doe');
INSERT INTO events (user_id, title, event_date, location) VALUES (1, 'Tech Conference 2025', '2025-03-15', 'San Francisco'), (1, 'Johnson Wedding', '2025-04-02', 'Beverly Hills');
INSERT INTO tasks (event_id, task_name, completed) VALUES (1, 'Confirm venue', 0), (1, 'Review catering', 1);
INSERT INTO budget (event_id, item_name, amount) VALUES (1, 'Venue', 1000.00), (2, 'Catering', 500.00);

INSERT INTO events (user_id, title, event_date, location) VALUES (1, 'Gala', '2025-05-10', 'NYC');
INSERT INTO budget (event_id, item_name, amount) VALUES (1, 'Decor', 500.00);
INSERT INTO orders (event_id, item_name, quantity, unit_price, status) VALUES (1, 'Chairs', 50, 10.00, 'pending');
INSERT INTO tasks (event_id, task_name, due_date, completed) VALUES (1, 'Book DJ', '2025-05-01', 0);