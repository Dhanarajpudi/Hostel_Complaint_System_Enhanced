
pipeline {
    agent any

    environment {
        PATH = "/Applications/Docker.app/Contents/Resources/bin:/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin:/usr/sbin:/sbin"
        MYSQL_DATABASE = "hostel_complaints"
        MYSQL_USER = "hostel_app"
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
                echo 'HostelCare source code checked out'
            }
        }

        stage('Validate Project') {
            steps {
                sh '''
                    set -eu
                    test -f Dockerfile
                    test -f compose.yaml
                    test -f database.sql
                    test -f index.php
                    echo "Project validation successful"
                '''
            }
        }

        stage('Validate Docker Compose') {
            steps {
                withCredentials([
                    string(credentialsId: 'hostelcare-mysql-root-password',
                           variable: 'MYSQL_ROOT_PASSWORD'),
                    string(credentialsId: 'hostelcare-mysql-password',
                           variable: 'MYSQL_PASSWORD')
                ]) {
                    sh 'docker compose config --quiet'
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                withCredentials([
                    string(credentialsId: 'hostelcare-mysql-root-password',
                           variable: 'MYSQL_ROOT_PASSWORD'),
                    string(credentialsId: 'hostelcare-mysql-password',
                           variable: 'MYSQL_PASSWORD')
                ]) {
                    sh 'docker compose build app'
                }
            }
        }

        stage('PHP Syntax Test') {
            steps {
                withCredentials([
                    string(credentialsId: 'hostelcare-mysql-root-password',
                           variable: 'MYSQL_ROOT_PASSWORD'),
                    string(credentialsId: 'hostelcare-mysql-password',
                           variable: 'MYSQL_PASSWORD')
                ]) {
                    sh '''
                        docker compose run --rm --no-deps app sh -c \
                        'find . -type f -name "*.php" -print0 | xargs -0 -n1 php -l'
                    '''
                }
            }
        }

        stage('Deploy with Docker Compose') {
            steps {
                withCredentials([
                    string(credentialsId: 'hostelcare-mysql-root-password',
                           variable: 'MYSQL_ROOT_PASSWORD'),
                    string(credentialsId: 'hostelcare-mysql-password',
                           variable: 'MYSQL_PASSWORD')
                ]) {
                    sh '''
                        docker compose up -d --build
                        docker compose ps
                    '''
                }
            }
        }

        stage('Verify Deployment') {
            steps {
                sh '''
                    set -eu

                    echo "Waiting for HostelCare application..."

                    curl --fail --silent --show-error \
                         --retry 12 --retry-delay 5 --retry-connrefused \
                         http://localhost:8081/ \
                         -o /dev/null

                    echo "HostelCare application is responding successfully"
                '''
            }
        }
    }

    post {
        success {
            echo 'HostelCare CI/CD deployment completed successfully!'
        }

        failure {
            echo 'HostelCare pipeline failed. Check Console Output.'
        }
    }
}

