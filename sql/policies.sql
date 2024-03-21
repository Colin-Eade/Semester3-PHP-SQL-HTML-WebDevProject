DROP TABLE IF EXISTS privacy_policy;
DROP TABLE IF EXISTS acceptable_use_policy;
DROP TABLE IF EXISTS terms_of_service;

CREATE TABLE privacy_policy (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    version INT NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_privacy_policy_last_updated ON privacy_policy (last_updated);
CREATE INDEX idx_privacy_policy_version ON privacy_policy (version);

CREATE TABLE acceptable_use_policy (
   id SERIAL PRIMARY KEY,
   content TEXT NOT NULL,
   version INT NOT NULL,
   last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_acceptable_use_policy_last_updated ON acceptable_use_policy (last_updated);
CREATE INDEX idx_acceptable_use_policy_version ON acceptable_use_policy (version);

CREATE TABLE terms_of_service (
   id SERIAL PRIMARY KEY,
   content TEXT NOT NULL,
   version INT NOT NULL,
   last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_terms_of_service_last_updated ON terms_of_service (last_updated);
CREATE INDEX idx_terms_of_service_version ON terms_of_service (version);