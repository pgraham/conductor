# Service definitions

Services can be defined as annotated classes. These classes, if placed in the
correct location will have RequestHandler implementations generated for them to
act as dispatchers and will have mappings added to the server.

All services need to have an `@Service` annotation:

    /**
     * Sample service definition.
     *
     * @Service
     */
    class MyService {
    }

This allows non-service support classes to exist along side the service
definition without confusing the compiler.

The methods of a service definition are mapped to requests using two
annotations: `@Method` and `@Uri`. These will be used to create a server mapping
which will invoke that method.

    /**
     * Sample service definition.
     *
     * @Service
     */
    class MyService {
        /**
         * @Method get
         * @Uri /info
         */
        public function getInfo() {
          return "Bite my shiny metal ass!";
        }
    }

Methods that don't have both annotations will be ignored.  Service methods must be public and parameterless.

Valid values for the `@Method` annotation are: DELETE, GET, POST, PUT
