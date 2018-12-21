pipeline {
  agent any
  stages {
    stage('Build') {
      agent {
        docker {
          image 'secom/composer'
          args '--volume $WORKSPACE:/app '
        }

      }
      environment {
        http_proxy = 'http://10.1.101.101:8080'
        https_proxy = 'http://10.1.101.101:8080'
      }
      steps {
        sh 'echo "Hello world"'
        sleep 1
        echo 'Printed message'
        sh 'composer install'
      }
    }
    stage('Artifactory') {
      steps {
        archiveArtifacts(artifacts: '**/**', allowEmptyArchive: true, fingerprint: true, onlyIfSuccessful: true)
      }
    }
  }
}