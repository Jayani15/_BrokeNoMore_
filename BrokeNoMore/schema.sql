CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE app_user (
  user_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  name text NOT NULL,
  email text NOT NULL UNIQUE,
  password text NOT NULL,
  created_at timestamptz NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE category (
  category_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  category_name text NOT NULL,
  category_type text
);

CREATE TABLE payment_method (
  payment_method_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  method_name text NOT NULL,
  details text
);

CREATE TABLE overall_budget (
  overall_budget_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id uuid NOT NULL REFERENCES app_user(user_id) ON DELETE CASCADE,
  budget_name text NOT NULL,
  total_amount numeric(12,2) NOT NULL DEFAULT 0,
  start_date date NOT NULL,
  end_date date NOT NULL
);

CREATE TABLE budget_details (
  detail_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  overall_budget_id uuid NOT NULL REFERENCES overall_budget(overall_budget_id) ON DELETE CASCADE,
  category_id uuid NOT NULL REFERENCES category(category_id) ON DELETE CASCADE,
  allocated_amount numeric(12,2) NOT NULL,
  spent_amount numeric(12,2) NOT NULL DEFAULT 0,
  remaining_amount numeric(12,2) NOT NULL
);

CREATE TABLE expense (
  expense_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id uuid NOT NULL REFERENCES app_user(user_id) ON DELETE CASCADE,
  overall_budget_id uuid REFERENCES overall_budget(overall_budget_id) ON DELETE SET NULL,
  category_id uuid REFERENCES category(category_id) ON DELETE SET NULL,
  payment_method_id uuid REFERENCES payment_method(payment_method_id) ON DELETE SET NULL,
  amount numeric(12,2) NOT NULL,
  description text,
  date date NOT NULL
);

CREATE TABLE savings (
  saving_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id uuid NOT NULL REFERENCES app_user(user_id) ON DELETE CASCADE,
  goal_name text,
  target_amount numeric(12,2) DEFAULT 0,
  saved_amount numeric(12,2) DEFAULT 0,
  start_date date
);

CREATE TABLE transaction (
  transaction_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  saving_id uuid REFERENCES savings(saving_id) ON DELETE CASCADE,
  amount numeric(12,2) NOT NULL,
  transaction_type text,
  transaction_date date NOT NULL DEFAULT CURRENT_DATE
);

CREATE TABLE notification (
  notification_id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id uuid REFERENCES app_user(user_id) ON DELETE CASCADE,
  message text,
  date date DEFAULT CURRENT_DATE,
  status text
);
