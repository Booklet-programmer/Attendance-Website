CREATE TABLE IF NOT EXISTS departments (
  department_id BIGSERIAL PRIMARY KEY,
  department_name VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS programmes (
  programme_id BIGSERIAL PRIMARY KEY,
  programme_name VARCHAR(180) NOT NULL,
  department_id BIGINT REFERENCES departments(department_id) ON DELETE RESTRICT,
  UNIQUE(programme_name, department_id)
);

CREATE TABLE IF NOT EXISTS academic_years (
  academic_year_id BIGSERIAL PRIMARY KEY,
  academic_year_name VARCHAR(30) NOT NULL UNIQUE,
  is_current BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS semesters (
  semester_id BIGSERIAL PRIMARY KEY,
  semester_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS classes (
  class_id BIGSERIAL PRIMARY KEY,
  class_name VARCHAR(100) NOT NULL,
  programme_id BIGINT REFERENCES programmes(programme_id) ON DELETE RESTRICT,
  class_level VARCHAR(30),
  UNIQUE(class_name, programme_id)
);

CREATE TABLE IF NOT EXISTS administrators (
  administrator_id BIGSERIAL PRIMARY KEY,
  full_name VARCHAR(160) NOT NULL,
  staff_id VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(180) NOT NULL UNIQUE,
  phone VARCHAR(40),
  department_id BIGINT NULL REFERENCES departments(department_id) ON DELETE SET NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role VARCHAR(30) NOT NULL CHECK (role IN ('Head Administrator','Lecturer')),
  account_status VARCHAR(20) NOT NULL DEFAULT 'Pending'
    CHECK (account_status IN ('Pending','Approved','Rejected','Suspended')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS students (
  student_id BIGSERIAL PRIMARY KEY,
  student_number VARCHAR(60) NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(180) UNIQUE,
  phone VARCHAR(40),
  department_id BIGINT REFERENCES departments(department_id) ON DELETE SET NULL,
  programme_id BIGINT REFERENCES programmes(programme_id) ON DELETE SET NULL,
  class_id BIGINT REFERENCES classes(class_id) ON DELETE SET NULL,
  password_hash TEXT NOT NULL,
  student_status VARCHAR(20) NOT NULL DEFAULT 'Active'
    CHECK (student_status IN ('Active','Inactive','Graduated','Suspended')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS courses (
  course_id BIGSERIAL PRIMARY KEY,
  course_code VARCHAR(30) NOT NULL UNIQUE,
  course_name VARCHAR(180) NOT NULL,
  department_id BIGINT REFERENCES departments(department_id) ON DELETE SET NULL,
  programme_id BIGINT REFERENCES programmes(programme_id) ON DELETE SET NULL,
  credit_hours SMALLINT NOT NULL DEFAULT 3 CHECK (credit_hours > 0)
);

CREATE TABLE IF NOT EXISTS teaching_assignments (
  assignment_id BIGSERIAL PRIMARY KEY,
  administrator_id BIGINT NOT NULL REFERENCES administrators(administrator_id) ON DELETE CASCADE,
  course_id BIGINT NOT NULL REFERENCES courses(course_id) ON DELETE RESTRICT,
  class_id BIGINT REFERENCES classes(class_id) ON DELETE SET NULL,
  academic_year_id BIGINT REFERENCES academic_years(academic_year_id) ON DELETE SET NULL,
  semester_id BIGINT REFERENCES semesters(semester_id) ON DELETE SET NULL,
  UNIQUE(administrator_id, course_id, class_id, academic_year_id, semester_id)
);

CREATE TABLE IF NOT EXISTS attendance_sessions (
  session_id BIGSERIAL PRIMARY KEY,
  assignment_id BIGINT NOT NULL REFERENCES teaching_assignments(assignment_id) ON DELETE RESTRICT,
  session_title VARCHAR(180) NOT NULL,
  session_date DATE NOT NULL DEFAULT CURRENT_DATE,
  starts_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  ends_at TIMESTAMPTZ,
  session_status VARCHAR(20) NOT NULL DEFAULT 'Active'
    CHECK (session_status IN ('Active','Completed','Cancelled')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS qr_tokens (
  qr_token_id BIGSERIAL PRIMARY KEY,
  session_id BIGINT NOT NULL REFERENCES attendance_sessions(session_id) ON DELETE CASCADE,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at TIMESTAMPTZ NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS attendance_records (
  attendance_id BIGSERIAL PRIMARY KEY,
  session_id BIGINT NOT NULL REFERENCES attendance_sessions(session_id) ON DELETE CASCADE,
  student_id BIGINT NOT NULL REFERENCES students(student_id) ON DELETE RESTRICT,
  check_in_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  attendance_status VARCHAR(20) NOT NULL DEFAULT 'Present'
    CHECK (attendance_status IN ('Present','Late','Absent')),
  UNIQUE(session_id, student_id)
);

CREATE TABLE IF NOT EXISTS system_activity_logs (
  log_id BIGSERIAL PRIMARY KEY,
  administrator_id BIGINT REFERENCES administrators(administrator_id) ON DELETE SET NULL,
  action_type VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_attendance_session_date ON attendance_sessions(session_date);
CREATE INDEX IF NOT EXISTS idx_attendance_student ON attendance_records(student_id);
CREATE INDEX IF NOT EXISTS idx_qr_session_expiry ON qr_tokens(session_id, expires_at);
CREATE INDEX IF NOT EXISTS idx_admin_username ON administrators(username);


-- Durable PHP sessions for stateless Vercel container instances.
CREATE TABLE IF NOT EXISTS app_sessions (
  session_id VARCHAR(128) PRIMARY KEY,
  session_data TEXT NOT NULL,
  expires_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_app_sessions_expiry ON app_sessions(expires_at);
