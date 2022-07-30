package gumBallSolution;

public abstract class StateGanball {

    public abstract void insertQuarter(GumBall gumBall );
    public abstract void turnCrank(GumBall gumBall );
    public abstract void ejectQuarter(GumBall gumBall);    
    public abstract void dispense(GumBall gumBall);

}