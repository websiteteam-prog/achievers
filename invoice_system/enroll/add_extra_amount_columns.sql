-- ============================================================
-- Extra Amount feature (admin can ADD an extra charge or
-- REDUCE the amount on a student's enrollment invoice).
--
-- Run these once on the live database before using the feature.
-- (discount_* columns are assumed to already exist.)
-- ============================================================

ALTER TABLE enrollment_inquiries
    ADD COLUMN extra_type        VARCHAR(20)    NOT NULL DEFAULT '' AFTER discount_description,
    ADD COLUMN extra_amount      DECIMAL(10,2)  NOT NULL DEFAULT 0  AFTER extra_type,
    ADD COLUMN extra_description VARCHAR(255)   NOT NULL DEFAULT '' AFTER extra_amount;

ALTER TABLE invoices
    ADD COLUMN extra_type        VARCHAR(20)    NOT NULL DEFAULT '' AFTER discount_description,
    ADD COLUMN extra_amount      DECIMAL(10,2)  NOT NULL DEFAULT 0  AFTER extra_type,
    ADD COLUMN extra_description VARCHAR(255)   NOT NULL DEFAULT '' AFTER extra_amount;
