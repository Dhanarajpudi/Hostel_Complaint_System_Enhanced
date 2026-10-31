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
                echo 'HostelCare source code checked out from GitHub'
                checkout scm
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

                    echo "Required project files verified"
                '''
            }
        }

        stage('Validate Docker Compose') {
            steps {
                withCredentials([
                    string(
                        credentialsId: 'hostelcare-mysql-root-password',
                        variable: 'MYSQL_ROOT_PASSWORD'
                    ),
                    string(
                        credentialsId: 'hostelcare-mysql-password',
                        variable: 'MYSQL_PASSWORD'
                    )
                ]) {
                    sh '''
                        docker compose config --quiet
                        echo "Docker Compose configuration is valid"
                    '''
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                withCredentials([
                    string(
                        credentialsId: 'hostelcare-mysql-root-password',
                        variable: 'MYSQL_ROOT_PASSWORD'
                    ),
                    string(
                        credentialsId: 'hostelcare-mysql-password',
                        variable: 'MYSQL_PASSWORD'
                    )
                ]) {
                    sh '''
                        docker compose build app
                    '''
                }
            }
        }

        stage('PHP Syntax Test') {
            steps {
                withCredentials([
                    string(
                        credentialsId: 'hostelcare-mysql-root-password',
                        variable: 'MYSQL_ROOT_PASSWORD'
                    ),
                    string(
                        credentialsId: 'hostelcare-mysql-password',
                        variable: 'MYSQL_PASSWORD'
                    )
                ]) {
                    sh '''
                        docker compose run --rm --no-deps app sh -c \
                        'find . -type f -name "*.php" -print0 | xargs -0 -n1 php -l'
                    '''
                }
            }
        }
    }

    post {
        success {
            echo 'HostelCare CI pipeline completed successfully!'
        }

        failure {
            echo 'HostelCare CI pipeline failed. Check the console output.'
        }
    }
}
