# TODO - Attendance System Future Features

## Employee Management
- [ ] Create `employee_profiles` table (employee_code, office_id, hire_date, base_salary)
- [ ] Add office relationship to User model
- [ ] Employee profile CRUD in HRD panel
- [ ] Employee import/export

## Face Recognition
- [ ] Integrate DeepFace via Docker for server-side face recognition
- [ ] Create `face_descriptors` table for storing face embeddings
- [ ] Camera capture and face encoding on clock-in/clock-out
- [ ] Face verification during attendance
- [ ] Anti-spoofing detection (liveness check)

## HR Features
- [ ] HRD panel for reviewing attendance records
- [ ] Approve/reject attendance with notes
- [ ] Bulk approve/reject
- [ ] Attendance reports and exports

## Leave Management
- [ ] Leave types (sick, personal, annual, etc.)
- [ ] Leave request form
- [ ] Leave approval workflow
- [ ] Leave balance tracking

## Overtime
- [ ] Overtime request form
- [ ] Overtime approval workflow
- [ ] Overtime calculation rules

## Payroll Integration
- [ ] Salary calculation based on attendance
- [ ] Tax calculation (PPH 21)
- [ ] Payroll periods
- [ ] Payslip generation

## Notifications
- [ ] Email notifications for HR when attendance needs review
- [ ] Push notifications for employees
- [ ] Leave request status updates

## Reporting
- [ ] Daily/weekly/monthly attendance reports
- [ ] Export to Excel/PDF
- [ ] Dashboard widgets for HR and Accounting

## Advanced Features
- [ ] Multi-office support
- [ ] Shift management
- [ ] Break time tracking
- [ ] Remote work support
- [ ] Mobile app integration
